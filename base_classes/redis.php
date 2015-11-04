<?php
/**
 * Object to deal with Redis clustering.
 */
class redisPool implements iCacheListEngine
{
	/**
	 * This will be automatically prepended onto all of our keys!
	 */
	const KEY_PREFIX = '__supreme_';
	/**
	 * Disconnect from our server after this long of inactivity / nothing.
	 */
	const REDIS_TIMEOUT = 100;
	/**
	 * Reconnect if we're this many seconds or less from our timeout.
	 */
	const REDIS_TIMEOUT_BUFFER = 6;

	/**
	 * Key List
	 * @var array
	 */
	private $keyList;
	/**
	 * Owner
	 * @var string
	 */
	private $owner;
	/**
	 * List of all of our servers' hosts and ports
	 * @var \RedisServerInfo
	 */
	private static $serverInfoList;
	private static $redisFileName;
	/**
	 * Our array of Redis objects
	 * @var \Redis
	 */
	private static $redisObjectList;
	/**
	 * List of times since we last started using each connection.
	 * @var int
	 */
	private static $timeoutList;

	/**
	 * Set our keyList
	 * @param array $keyList
	 */
	public function __construct(array $keyList, $owner = null)
	{
		$this->keyList = $keyList;
		$this->owner = $owner;
	}
	
	/**
	 * Return a RedisKey object
	 * @param string $key
	 * @return \redisKey
	 */
	public static function getRedisKey($key, $autoLock = false)
	{
		return new redisKey($key, $autoLock);
	}

	/**
	 * Get a RedisServerInfo object for a specific key
	 * @param type $key
	 * @return \RedisServerInfo|boolean
	 */
	public static function getServerInfoByKey($key, $owner = null)
	{

		$hostList = self::getConnectionInfo($owner);
		return $hostList[self::getServerIndexByKey($key, $owner)];
	}

	public static function getServerIndexByKey($key, $owner = null)
	{
		$hostList = self::getConnectionInfo($owner);

		//Support for "(realkey)fake_key_lololol".  It will only use "realkey" to determine the host
		$matches = null;
		if (preg_match('/^\(([^)]+)\).*/', $key, $matches) > 0)
		{
			$key = $matches[1];
		}

		//sprintf is used to make signed values on 32 bit systems unsigned
		return sprintf('%u', crc32($key)) * count($hostList) / 0xFFFFFFFF;
	}

	/**
	 * Get instance of host.
	 *
	 * @param string $serverInfo
	 * @return \Redis
	 */
	public static function getRedisInstance(RedisServerInfo $serverInfo)
	{
		
//		if ($redis = dataStore::getObject($serverInfo->getUniqueIdentifier(), 'Redis Socket Buffer'))
//		{
//			return $redis;
//		}
//		else
//		{
//			//Create new connection
//			$redis = new Redis();
//			try
//			{
//				$redis->connect($serverInfo->getHost(), $serverInfo->getPort(), self::REDIS_TIMEOUT);
//			}
//			catch (RedisException $ex)
//			{
//				throw new error(errorCodes::ERROR_COULD_NOT_CONNECT_TO_CACHE);
//			}
//			dataStore::setObject($serverInfo->getUniqueIdentifier(), $redis, 'Redis Socket Buffer');
//			return $redis;
//		}
		
		//Do we have an already established connection?
		if (isset(self::$redisObjectList[$serverInfo->getUniqueIdentifier()]))
		{
			//Figure out if we're about to expire
			$timeSinceLastRequest = time() - self::$timeoutList[$serverInfo->getUniqueIdentifier()];
			if ($timeSinceLastRequest < self::REDIS_TIMEOUT - self::REDIS_TIMEOUT_BUFFER)
			{
				$redis = self::$redisObjectList[$serverInfo->getUniqueIdentifier()];
				try
				{
					if (!$redis->isConnected() && !$redis->connect($serverInfo->getHost(), $serverInfo->getPort(), self::REDIS_TIMEOUT))
					{
						throw new error(errorCodes::ERROR_COULD_NOT_CONNECT_TO_CACHE);
					}
				}
				catch (RedisException $ex)
				{
					throw new error(errorCodes::ERROR_COULD_NOT_CONNECT_TO_CACHE);
				}

				self::$timeoutList[$serverInfo->getUniqueIdentifier()] = time();
				return $redis;
			}

			self::$redisObjectList[$serverInfo->getUniqueIdentifier()]->close();
		}

		//Create new connection
		$redis = new Redis();
		try
		{
			$redis->connect($serverInfo->getHost(), $serverInfo->getPort(), self::REDIS_TIMEOUT);
		}
		catch (RedisException $ex)
		{
			throw new error(errorCodes::ERROR_COULD_NOT_CONNECT_TO_CACHE);
		}
		self::$redisObjectList[$serverInfo->getUniqueIdentifier()] = $redis;
		self::$timeoutList[$serverInfo->getUniqueIdentifier()] = time();

		return $redis;
	}

	/**
	 *
	 * @return array
	 * @throws error
	 */
	private static function getConnectionInfo($owner = null)
	{
		if (!empty($owner))
		{
                        // TODO
                        // redis connection info...
			$redisFile = 'app0';
			if (!$redisFile)
			{
				$redisFile = 'noOwner';
			}
		}
		else
		{
			$redisFile = 'noOwner';
		}

		//Check if our config file exists
		if (!is_file($_SERVER['_HTDOCS_'] . '/configs/database/cache/Redis_' . $redisFile . '.php'))
		{
			error::addError('Missing Redis configuration file.' . $redisFile);
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		//Check if our config file has our settings in it
		require $_SERVER['_HTDOCS_'] . '/configs/database/cache/Redis_' . $redisFile . '.php';
		if (!isset($redisServerList))
		{
			error::addError('Missing Redis configuration array.');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		//Our settings should be arrays
		if (!is_array($redisServerList))
		{
			error::addError('Invalid Redis configuration; settings are not arrays.');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		//Make sure data in our settings
		if (empty($redisServerList))
		{
			error::addError('Empty Redis configuration.');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		$hostList = array();
		foreach ($redisServerList as $key => $hostString)
		{
			$hostList[] = RedisServerInfo::fromHostString($hostString);
		}

		self::$redisFileName = $redisFile;
		self::$serverInfoList = $hostList;
		return $hostList;
	}
	##
	# Start of RedisArray function implementations
	##

	public static function flushDB($database = null)
	{
		foreach (self::getConnectionInfo() as $redisServerInfo)
		{
			$redis = self::getRedisInstance($redisServerInfo);
			/* @var $redis Redis */

			if (!is_null($database) && $redis->getDBNum() !== $database)
			{
				try
				{
					if (!$redis->select($database))
					{
						error::addError('Failed to switch Redis databases.');
						throw new error(errorCodes::ERROR_INTERNAL_ERROR);
					}
				}
				catch (RedisException $ex)
				{
					error::addError('Failed to switch Redis databases.');
					throw new error(errorCodes::ERROR_INTERNAL_ERROR);
				}
			}

			try
			{
				if (!$redis->flushDB())
				{
					error::addError('Failed to flush Redis database.');
					throw new error(errorCodes::ERROR_INTERNAL_ERROR);
				}
			}
			catch (RedisException $ex)
			{
				error::addError('Failed to flush Redis database.');
				throw new error(errorCodes::ERROR_INTERNAL_ERROR);
			}
		}

		return true;
	}

	public function multiDelete()
	{
		$returnList = array();
		foreach ($this->groupKeysByServer() as $serverInfoIndex => $keyList)
		{
			//Get Redis for this host
			$redis = self::getRedisInstance(self::$serverInfoList[$serverInfoIndex]);

			try
			{
				//Set database to cache data database
				if ($redis->getDBNum() !== redisKey::DATABASE_CACHE_DATA && !$redis->select(redisKey::DATABASE_CACHE_DATA))
				{
					error::addError('Failed to switch Redis databases.');
					throw new error(errorCodes::ERROR_INTERNAL_ERROR);
				}

				//Format keyList
				$formattedKeyList = array_map(array('self', 'getValueKey'), $keyList);

				//Do action
				$result = ($redis->del($formattedKeyList) > 0);
				foreach ($keyList as $key => $value)
				{
					$returnList[$keyList[$key]] = $result;
				}
			}
			catch (RedisException $e)
			{
				throw new error(errorCodes::ERROR_INTERNAL_ERROR);
			}
		}

		return $returnList;
	}

	public function multiGet()
	{
		$returnList = array();
		foreach ($this->groupKeysByServer() as $serverInfoIndex => $keyList)
		{
			//Get Redis for this host
			$redis = self::getRedisInstance(self::$serverInfoList[$serverInfoIndex]);

			try
			{
				//Set database to cache data database
				if ($redis->getDBNum() !== redisKey::DATABASE_CACHE_DATA && !$redis->select(redisKey::DATABASE_CACHE_DATA))
				{
					error::addError('Failed to switch Redis databases.');
					throw new error(errorCodes::ERROR_INTERNAL_ERROR);
				}

				//Format keyList
				$formattedKeyList = array_map(array('self', 'getValueKey'), $keyList);

				//Do action
				$return = $redis->mget($formattedKeyList);
				foreach ($return as $key => $result)
				{
					$returnList[$keyList[$key]] = $result;
				}
			}
			catch (RedisException $e)
			{
				throw new error(errorCodes::ERROR_INTERNAL_ERROR);
			}
		}

		return $returnList;
	}

	public function multiSet(array $valueList, $expiration = 0)
	{
		if (count($valueList) !== count($this->keyList))
		{
			error::addError('Invalid key => value count in redis multi set.');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		if (count(array_diff($this->keyList, array_keys($valueList))) > 0)
		{
			error::addError('Invalid valueList in redis multi set; not all cacheKeys present.');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		$returnList = array();
		foreach ($this->groupKeysByServer() as $serverInfoIndex => $keyList)
		{
			//Get Redis for this host
			$redis = self::getRedisInstance(self::$serverInfoList[$serverInfoIndex]);

			try
			{
				//Start pipelined "transaction"
				$redis = $redis->multi(redis::PIPELINE);

				//Set database to cache data database
				$redisDBChangeShift = 0;
				if ($redis->getDBNum() !== redisKey::DATABASE_CACHE_DATA)
				{
					$redis = $redis->select(redisKey::DATABASE_CACHE_DATA);
					$redisDBChangeShift = 1;
				}

				//Add commands
				foreach ($keyList as $key => $value)
				{
					if ($expiration > 0)
					{
						$redis = $redis->setex(self::getValueKey($value), $expiration, $valueList[$value]);
					}
					else
					{
						$redis = $redis->set(self::getValueKey($value), $valueList[$value]);
					}
				}

				//Run!
				$return = $redis->exec();
			}
			catch (RedisException $e)
			{
				throw new error(errorCodes::ERROR_INTERNAL_ERROR);
			}

			//Parse results
			foreach ($keyList as $key => $cacheKey)
			{
				$returnList[$cacheKey] = $return[$key + $redisDBChangeShift];
			}
		}

		return $returnList;
	}

	/**
	 * From our current $this->keyList, determine all host Fmappings, and return
	 * arrays in a array(host => $keyList, host2 => $keyList) format.
	 *
	 * @return array
	 */
	private function groupKeysByServer()
	{
		if (!is_array($this->keyList) || count($this->keyList) === 0)
		{
			return array();
		}

		$return = array();
		foreach ($this->keyList as $cacheKey)
		{
			$server = self::getServerIndexByKey($cacheKey, $this->owner);
			if (!isset($return[$server]))
			{
				$return[$server] = array();
			}
			$return[$server][] = $cacheKey;
		}
		return $return;
	}

	/**
	 * Get base Redis DB key with any applicable prefix or versioning for our $cacheKey
	 *
	 * @param string $cacheKey
	 * @return type
	 */
	public static function getBaseKey($cacheKey)
	{
		return redisPool::KEY_PREFIX . '_' . $cacheKey;
	}

	/**
	 * Get value Redis DB key with any applicable prefix or versioning for our $cacheKey
	 *
	 * @param string $cacheKey
	 * @return string
	 */
	public static function getValueKey($cacheKey)
	{
		return self::getBaseKey($cacheKey) . '__value';
	}

	/**
	 * Get lock Redis DB key with any applicable prefix or versioning for our $cacheKey
	 *
	 * @param string $cacheKey
	 * @return string
	 */
	public static function getLockKey($cacheKey)
	{
		return self::getBaseKey($cacheKey) . '__lock';
	}
}
class redisKey implements cacheEntryEngine
{
	/**
	 * Database Redis defaults to
	 */
	const DATABASE_DEFAULT = self::DATABASE_META;
	/**
	 * Database we store our meta data in.
	 */
	const DATABASE_META = 0;
	/**
	 * Database we store our cached / volitile data in.
	 */
	const DATABASE_CACHE_DATA = 1;
	/**
	 * Database we store Group data in.
	 */
	const DATABASE_GROUPS = 1;
	/**
	 * Database we store our Request logs / MARC2.0 data
	 */
	const DATABASE_REQUEST_LOG = 0;

	/**
	 * The name of the host we are currently using.
	 * @var \RedisServerInfo
	 */
	private $serverInfo;
	/**
	 * Our key used to determine what instance we are on.
	 * @var string
	 */
	private $key;
	/**
	 * What our cache lock value is.
	 * @var string
	 */
	private $lockToken;
	/**
	 * Do we currently have a cache lock?
	 * @var bool
	 */
	private $isLocked = false;

	/**
	 * Create an instance of our redisKey object
	 *
	 * @param string $key
	 * @param bool $autoLock
	 */
	public function __construct($key, $autoLock = false, $owner = null)
	{
		$this->key = $key;
		$this->serverInfo = redisPool::getServerInfoByKey($key, $owner);
		if ($autoLock)
		{
			$this->lock();
		}
	}

	/**
	 * Unlock if we're still locked.
	 */
	public function __destruct()
	{
		if ($this->isLocked())
		{
			$this->unlock();
		}
	}

	public function zAdd($score, $member, $database = self::DATABASE_CACHE_DATA)
	{
		$this->select($database);

		try
		{
			return $this->getRedis()->zAdd($this->getValueKey(), $score, $member);
		}
		catch (RedisException $e)
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}
	}

	public function zDeleteRangeByScore($start, $end, $database = self::DATABASE_CACHE_DATA)
	{
		$this->select($database);

		try
		{
			return $this->getRedis()->zDeleteRangeByScore($this->getValueKey(), $start, $end);
		}
		catch (RedisException $exc)
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}
	}

	public function zRangeByScore($start, $end, $options = array('withscores' => TRUE), $database = self::DATABASE_CACHE_DATA)
	{
		$this->select($database);

		try
		{
			return $this->getRedis()->zRangeByScore($this->getValueKey(), $start, $end, $options);
		}
		catch (RedisException $e)
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}
	}

	public function zCount($start = '-inf', $end = '+inf', $database = self::DATABASE_CACHE_DATA)
	{
		$this->select($database);

		try
		{
			return $this->getRedis()->zCount($this->getValueKey(), $start, $end);
		}
		catch (RedisException $e)
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}
	}

	public function zIncrBy($member, $value = 1.0, $database = self::DATABASE_CACHE_DATA)
	{
		$this->select($database);

		try
		{
			return $this->getRedis()->zIncrBy($this->getValueKey(), $value, $member);
		}
		catch (RedisException $e)
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}
	}

	public function zScore($member, $database = self::DATABASE_CACHE_DATA)
	{
		$this->select($database);

		try
		{
			return $this->getRedis()->zScore($this->getValueKey(), $member);
		}
		catch (RedisException $e)
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}
	}

	public function setTimeout($time, $database = self::DATABASE_CACHE_DATA)
	{
		$this->select($database);

		try
		{
			return $this->getRedis()->setTimeout($this->getValueKey(), $time);
		}
		catch (RedisException $e)
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}
	}

	/**
	 * Gets a lock on our cache key.
	 * @return boolean
	 */
	public function lock()
	{
		if ($this->isLocked())
		{
			return true;
		}

		try
		{
			$this->select(self::DATABASE_META);

			//We want a new key for each lock
			$this->resetLockToken();

			$key = $this->getLockKey();
			$value = $this->getLockToken();
			$optionList = array('nx', 'ex' => self::LOCK_LIFETIME);

			//Attempt to get a lock.  False means the key already exists in this case
			while ($this->getRedis()->set($key, $value, $optionList) === false)
			{
				//Sleep 10 milliseconds
				usleep(10 * 1000);
			}
		}
		catch (RedisException $e)
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		$this->isLocked = true;
		return true;
	}

	/**
	 * Unlock our cache key if we're currently locked
	 * @return boolean
	 */
	public function unlock()
	{
		if (!$this->isLocked())
		{
			return true;
		}

		try
		{
			$this->select(self::DATABASE_META);

			// Watch our key, check if it's still our value, and delete it only if it stays our value through-out the entire thing.
			$this->getRedis()->watch($this->getLockKey());
			$value = $this->getRedis()->get($this->getLockKey());
			if ($value !== false && $value == $this->getLockToken())
			{
				//Atomically delete our cache key, but only if it's actually ours.  #magic
				$this->getRedis()->multi()->del($this->getLockKey())->exec();
			}
		}
		catch (RedisException $e)
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		$this->isLocked = false;
		return true;
	}

	/**
	 * Are we currently locked
	 * @return bool
	 */
	public function isLocked()
	{
		return $this->isLocked;
	}

	/**
	 * The key we use for our lock in Redis
	 * @return string
	 */
	private function getLockKey()
	{
		return redisPool::getLockKey($this->key);
	}

	/**
	 * The key was save / read our value from in Redis
	 * @return string
	 */
	private function getValueKey()
	{
		return redisPool::getValueKey($this->key);
	}

	/**
	 * Fetches lock token, and generates one if one does not exist.
	 * @return string
	 */
	private function getLockToken()
	{
		if (is_null($this->lockToken))
		{
			$this->lockToken = str_pad(dechex(mt_rand(0, 4294967296)), 8, '0', STR_PAD_LEFT);
		}

		return $this->lockToken;
	}

	/**
	 * Reset our lockToken to the default value.
	 */
	private function resetLockToken()
	{
		if (is_null($this->lockToken))
		{
			return;
		}

		$this->lockToken = null;
	}
	##
	# Start of RedisArray function implementations
	##

	/**
	 *
	 * @param int $database
	 * @return boolean
	 */
	public function delete($database = self::DATABASE_CACHE_DATA)
	{
		$this->select($database);

		try
		{
			return $this->getRedis()->delete($this->getValueKey());
		}
		catch (RedisException $e)
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}
	}

	/**
	 *
	 * @param int $database
	 * @return string|boolean
	 */
	public function get($database = self::DATABASE_CACHE_DATA)
	{
		$this->select($database);

		try
		{
			return $this->getRedis()->get($this->getValueKey());
		}
		catch (RedisException $e)
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}
	}

	/**
	 *
	 * @param string $value
	 * @param int $expiration
	 * @param int $database
	 * @return boolean
	 */
	public function setIfNotExist($value, $expiration = null, $database = self::DATABASE_CACHE_DATA)
	{
		$optionList = array('nx');
		if (!is_null($expiration))
		{
			$optionList['ex'] = $expiration;
		}

		return $this->set($value, $optionList, $database);
	}

	/**
	 *
	 * @param string $value
	 * @param int $expiration
	 * @param int $database
	 * @return boolean
	 */
	public function setIfExist($value, $expiration = null, $database = self::DATABASE_CACHE_DATA)
	{
		$optionList = array('xx');
		if (!is_null($expiration))
		{
			$optionList['ex'] = $expiration;
		}

		return $this->set($value, $optionList, $database);
	}

	/**
	 *
	 * @param string $value
	 * @param array $optionList
	 * @param int $database
	 * @return boolean
	 */
	public function set($value, array $optionList = array(), $database = self::DATABASE_CACHE_DATA)
	{
		$this->select($database);

		try
		{
			return $this->getRedis()->set($this->getValueKey(), $value, $optionList);
		}
		catch (RedisException $e)
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}
	}

	/**
	 *
	 * @param string $value
	 * @param int $expiration
	 * @param int $database
	 * @return boolean
	 */
	public function setWithExpiration($value, $expiration, $database = self::DATABASE_CACHE_DATA)
	{
		$this->select($database);

		try
		{
			return $this->getRedis()->setex($this->getValueKey(), $expiration, $value);
		}
		catch (RedisException $e)
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}
	}

	/**
	 * Select what database to write to / read from
	 * @param int $database
	 * @return boolean
	 */
	public function select($database)
	{
		if ($this->getCurrentDatabase() === $database)
		{
			return true;
		}

		try
		{
			if (!$this->getRedis()->select($database))
			{
				error::addError('Failed to switch Redis databases.');
				throw new error(errorCodes::ERROR_INTERNAL_ERROR);
			}
		}
		catch (RedisException $e)
		{
			error::addError('Failed to switch Redis databases.');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		return true;
	}

	public function getCurrentDatabase()
	{
		return $this->getRedis()->getDBNum();
	}

	public function decrement($amount = 1, $database = self::DATABASE_CACHE_DATA)
	{
		$this->select($database);

		try
		{
			return $this->getRedis()->decrBy($this->getValueKey(), $amount);
		}
		catch (RedisException $e)
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}
	}

	public function increment($amount = 1, $database = self::DATABASE_CACHE_DATA)
	{
		$this->select($database);

		try
		{
			return $this->getRedis()->incrBy($this->getValueKey(), $amount);
		}
		catch (RedisException $e)
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}
	}

	/**
	 *
	 * @param int $option Use predefined constants available in the Redis class.
	 * @return string
	 */
	public function info($option = null)
	{
		try
		{
			return $this->getRedis()->info($option);
		}
		catch (RedisException $e)
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}
	}

	public function ping()
	{
		try
		{
			return $this->getRedis()->ping();
		}
		catch (RedisException $e)
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}
	}

	public function flushDB()
	{
		try
		{
			return $this->getRedis()->flushDB();
		}
		catch (RedisException $e)
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}
	}

	public function flushAll()
	{
		try
		{
			return $this->getRedis()->flushAll();
		}
		catch (RedisException $e)
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}
	}

	public function keys($pattern)
	{
		try
		{
			return $this->getRedis()->keys($pattern);
		}
		catch (RedisException $e)
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}
	}

	public function getOption($name)
	{
		try
		{
			return $this->getRedis()->getOption($name);
		}
		catch (RedisException $e)
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}
	}

	public function setOption($name, $value)
	{
		try
		{
			return $this->getRedis()->setOption($name, $value);
		}
		catch (RedisException $e)
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}
	}

	private function getRedis()
	{
		return redisPool::getRedisInstance($this->serverInfo);
	}
}

class RedisServerInfo
{
	/**
	 * The default port to connect to redis on.
	 */
	const DEFAULT_PORT = 6379;

	private $host;
	private $port;

	public function __construct($host, $port = null)
	{
		$this->host = $host;

		if ($port === null)
		{
			$port = self::DEFAULT_PORT;
		}
		$this->port = $port;
	}

	public function getHost()
	{
		return $this->host;
	}

	public function getPort()
	{
		return $this->port;
	}

	public function getUniqueIdentifier()
	{
		return $this->host . ':' . $this->port;
	}

	public static function fromHostString($hostString)
	{
		if (strpos($hostString, ':') === false)
		{
			$host = $hostString;
			$port = self::DEFAULT_PORT;
		}
		else
		{
			$info = explode(':', $hostString);
			$host = $info[0];
			$port = $info[1];
		}

		if (empty($host) || $port <= 0)
		{
			error::addError('Bad Redis configuration entry "' . $hostString . '".');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		return new RedisServerInfo($host, $port);
	}
}