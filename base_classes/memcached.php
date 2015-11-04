<?php

/**
 * @property Memcached $memcachedInstance MemcacheD object.
 */
class memcachedPool implements iCacheListEngine
{
	/**
	 * This will be prepended onto all of our keys!
	 */
	const KEY_PREFIX = '__supreme_';

	/**
	 * Key List
	 * @var array
	 */
	private $keyList;
	/**
	 * Ownership
	 * @var array
	 */
	private $owner;
	/**
	 *
	 * @var Memcached
	 */
	private static $memcachedInstance;

	public function __construct(array $keyList, $owner = null)
	{
		$this->keyList = $keyList;
		$this->owner = $owner;
		self::singleton();
	}

	/**
	 *
	 * @return Memcached
	 */
	public static function singleton()
	{
		if (is_null(self::$memcachedInstance))
		{
			self::$memcachedInstance = self::getMemcachedInstance();
		}
		return self::$memcachedInstance;
	}

	private static function getMemcachedInstance()
	{
		$instance = new Memcached();

		$instance->setOptions(
			array(
				Memcached::OPT_COMPRESSION => false,
				Memcached::OPT_DISTRIBUTION => Memcached::DISTRIBUTION_CONSISTENT,
				Memcached::OPT_LIBKETAMA_COMPATIBLE => true,
				memcached::OPT_BINARY_PROTOCOL => true
			)
		);

		if (!$instance->addServers(self::getPoolInfo()))
		{
			error:addError('Could not attach memcached pool');
			throw new error(errorCodes::ERROR_COULD_NOT_CONNECT_TO_CACHE);
		}


		return $instance;
	}

	private static function getPoolInfo()
	{
		$memcache = null;
		//Check if our config file exists
		if (!is_file($_SERVER['_HTDOCS_'] . '/configs/database/cache/Memcached.php'))
		{
			error::addError('Missing Memcached configuration file.');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}
		require $_SERVER['_HTDOCS_'] . '/configs/database/cache/Memcached.php';
		if (!is_array($memcache))
		{
			error::addError('Invalid Memcached configuration');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		return $memcache;
	}

	/**
	 * Our base key.
	 *
	 * @param string $key
	 * @return string
	 */
	public static function getBaseKey($key)
	{
		return self::KEY_PREFIX . '_' . cacheEntry::CACHE_VERSION . '_' . $key;
	}

	/**
	 * The key we use for our value.
	 *
	 * @param string $key
	 * @return string
	 */
	public static function getValueKey($key)
	{
		return self::getBaseKey($key) . '__value';
	}

	/**
	 * The key we use for our lock.
	 *
	 * @param string $key
	 * @return string
	 */
	public static function getLockKey($key)
	{
		return self::getBaseKey($key) . '__lock';
	}

	public static function flushAll()
	{
		return self::singleton()->flush();
	}

	public static function add($key, $value, $expiration = 0)
	{
		return self::singleton()->add($key, $value, $expiration);
	}

	public static function set($key, $value, $expiration = 0)
	{
		return self::singleton()->set($key, $value, $expiration);
	}

	public static function setByKey($server_key, $key, $value, $expiration = 0)
	{
		return self::singleton()->setByKey($server_key, $key, $value, $expiration);
	}

	public static function addByKey($server_key, $key, $value, $expiration = 0)
	{
		return self::singleton()->addByKey($server_key, $key, $value, $expiration);
	}

	public static function append($key, $value)
	{
		return self::singleton()->append($key, $value);
	}

	public static function appendByKey($server_key, $key, $value)
	{
		return self::singleton()->appendByKey($server_key, $key, $value);
	}

	public static function cas($cas_token, $key, $value, $expiration = 0)
	{
		return self::singleton()->cas($cas_token, $key, $value, $expiration);
	}

	public static function casByKey($cas_token, $server_key, $key, $value, $expiration)
	{
		return self::singleton()->casByKey($cas_token, $server_key, $key, $value, $expiration);
	}

	public static function decrement($key, $offset = 1, $initialValue = -1, $expiration = 0)
	{
		return self::singleton()->decrement($key, $offset, $initialValue, $expiration);
	}

	public static function decrementByKey($server_key, $key, $offset = 1, $initialValue = -1, $expiration = 0)
	{
		return self::singleton()->decrementByKey($server_key, $key, $offset, $initialValue, $expiration);
	}

	public static function delete($key, $time = 0)
	{
		return self::singleton()->delete($key, $time);
	}

	public static function deleteByKey($server_key, $key, $time = 0)
	{
		return self::singleton()->deleteByKey($server_key, $key, $time);
	}

	public static function deleteMulti(array $keyList, $time = 0)
	{
		return self::singleton()->deleteMulti($keyList, $time);
	}

	public static function deleteMultiByKey($server_key, array $keyList, $time = 0)
	{
		return self::singleton()->deleteMultiByKey($server_key, $keyList, $time);
	}

	public static function get($key, &$casToken = null)
	{
		return self::singleton()->get($key, null, $casToken);
	}

	public static function getByKey($server_key, $key, &$casToken = null)
	{
		return self::singleton()->getByKey($server_key, $key, null, $casToken);
	}

	public static function getDelayed($keys)
	{
		return self::singleton()->getDelayed($keys);
	}

	public static function getDelayedByKey($server_key, $keys)
	{
		return self::singleton()->getDelayedByKey($server_key, $keys);
	}

	public static function getMulti(array $items, array &$casTokenList = null)
	{
		return self::singleton()->getMulti($items, $casTokenList);
	}

	public static function getMultiByKey($server_key, array $items, array &$casTokenList = null)
	{
		return self::singleton()->getMultiByKey($server_key, $items, $casTokenList);
	}

	public static function getServerByKey($key)
	{
		return self::singleton()->getServerByKey($key);
	}

	public static function getServerList()
	{
		return self::singleton()->getServerList();
	}

	public static function getStats()
	{
		return self::singleton()->getStats();
	}

	public static function increment($key, $offset = 1, $initialValue = 1, $expiration = 0)
	{
		return self::singleton()->increment($key, $offset, $initialValue, $expiration);
	}

	public static function incrementByKey($server_key, $key, $offset = 1, $initialValue = 1, $expiration = 0)
	{
		return self::singleton()->incrementByKey($server_key, $key, $offset, $initialValue, $expiration);
	}

	public static function prepend($key, $value)
	{
		return self::singleton()->prepend($key, $value);
	}

	public static function prependByKey($server_key, $key, $value)
	{
		return self::singleton()->prependByKey($server_key, $key, $value);
	}

	public static function replace($key, $value, $expiration = 0)
	{
		return self::singleton()->replace($key, $value, $expiration);
	}

	public static function replaceByKey($server_key, $key, $value, $expiration = 0)
	{
		return self::singleton()->replaceByKey($server_key, $key, $value, $expiration);
	}

	public static function setMulti(array $items, $expiration = 0)
	{
		return self::singleton()->setMulti($items, $expiration);
	}

	public static function setMultiByKey($server_key, array $items, $expiration = 0)
	{
		return self::singleton()->setMultiByKey($server_key, $items, $expiration);
	}

	public static function setOption($option, $value)
	{
		return self::singleton()->setOption($option, $value);
	}

	public static function setOptions(array $optionList)
	{
		return self::singleton()->setOptions($optionList);
	}

	public static function getResultCode()
	{
		return self::singleton()->getResultCode();
	}

	public static function getResultMessage()
	{
		return self::singleton()->getResultMessage();
	}

	public function multiDelete()
	{
		$returnList = array();
		foreach ($this->groupKeysByServer() as $keyList)
		{
			//Format keyList
			$formattedKeyList = array_map(array('self', 'getValueKey'), $keyList);

			//Do action
			$result = self::deleteMultiByKey(current($keyList), $formattedKeyList);
			foreach ($keyList as $value)
			{
				$returnList[$value] = $result[self::getValueKey($value)];
			}
		}

		return $returnList;
	}

	public function multiGet()
	{
		$returnList = array();
		foreach ($this->groupKeysByServer() as $keyList)
		{
			//Format keyList
			$formattedKeyList = array_map(array('self', 'getValueKey'), $keyList);

			//Do action
			$result = self::getMultiByKey(current($keyList), $formattedKeyList);
			foreach ($keyList as $value)
			{
				if (isset($result[self::getValueKey($value)]))
				{
					$returnList[$value] = $result[self::getValueKey($value)];
				}
				else
				{
					$returnList[$value] = false;
				}
			}
		}

		return $returnList;
	}

	public function multiSet(array $valueList, $expiration = null)
	{
		if (count($valueList) !== count($this->keyList))
		{
			error::addError('Invalid key => value count in memcached multi set.');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		if (count(array_diff($this->keyList, array_keys($valueList))) > 0)
		{
			error::addError('Invalid valueList in memcached multi set; not all cacheKeys present.');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		$returnList = array();
		foreach ($this->groupKeysByServer() as $keyList)
		{
			//Format keyList
			$formattedKeyList = array();
			foreach ($keyList as $cacheKey)
			{
				$formattedKeyList[self::getValueKey($cacheKey)] = $valueList[$cacheKey];
			}

			//Do action
			$result = self::setMultiByKey(current($keyList), $formattedKeyList, $expiration);
			foreach ($keyList as $key => $value)
			{
				$returnList[$keyList[$key]] = $result;
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
			$serverInfo = self::getServerByKey($cacheKey);
			$server = $serverInfo['host'] . ':' . $serverInfo['port'];
			if (!isset($return[$server]))
			{
				$return[$server] = array();
			}
			$return[$server][] = $cacheKey;
		}

		return $return;
	}
}

class memcachedKey implements cacheEntryEngine
{
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
	 *
	 * @param Redis $redisInstance
	 * @param string $key
	 * @throws error
	 */
	public function __construct($key, $autoLock = false)
	{
		$this->key = $key;

		if ($autoLock)
		{
			$this->lock();
		}
	}

	/**
	 * Unlock if we're still locked
	 */
	public function __destruct()
	{
		if ($this->isLocked())
		{
			$this->unlock();
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

		//We want a new key for each lock
		$this->resetLockToken();

		//Get a lock
		while (true)
		{
			$casToken = null;
			$data = memcachedPool::getByKey($this->getBaseKey(), $this->getLockKey(), $casToken);
			if (memcachedPool::getResultCode() == Memcached::RES_NOTFOUND)
			{
				if (memcachedPool::addByKey($this->getBaseKey(), $this->getLockKey(), $this->getLockToken(), static::LOCK_LIFETIME))
				{
					break;
				}
			}
			elseif ($data === false)
			{
				if (memcachedPool::casByKey($casToken, $this->getBaseKey(), $this->getLockKey(), $this->getLockToken(), static::LOCK_LIFETIME))
				{
					break;
				}
			}

			//Sleep 10 milliseconds
			usleep(10 * 1000);
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

		$casToken = null;
		if ($this->getLockToken() == memcachedPool::getByKey($this->getBaseKey(), $this->getLockKey(), $casToken))
		{
			memcachedPool::casByKey($casToken, $this->getBaseKey(), $this->getLockKey(), false, 1);
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
	 * Our base key.
	 * @return string
	 */
	private function getBaseKey()
	{
		return memcachedPool::getBaseKey($this->key);
	}

	/**
	 * The key we use for our lock.
	 * @return string
	 */
	private function getLockKey()
	{
		return memcachedPool::getLockKey($this->key);
	}

	/**
	 * The key we used for the actual value.
	 * @return string
	 */
	private function getValueKey()
	{
		return memcachedPool::getValueKey($this->key);
	}

	/**
	 * Fetches lock token, and generates one if one does not exist.
	 * @param boolean $generateNew
	 * @return string
	 */
	private function getLockToken($generateNew = true)
	{
		if (is_null($this->lockToken) && $generateNew)
		{
			$this->lockToken = dechex(mt_rand(0, 4294967296));
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

	/**
	 *
	 * @return boolean
	 */
	public function delete()
	{
		return memcachedPool::deleteByKey($this->getBaseKey(), $this->getValueKey());
	}

	/**
	 *
	 * @return string|boolean
	 */
	public function get()
	{
		return memcachedPool::getByKey($this->getBaseKey(), $this->getValueKey());
	}

	/**
	 *
	 * @param string $value
	 * @param int $expiration
	 * @return boolean
	 */
	public function setIfNotExist($value, $expiration = null)
	{
		return memcachedPool::addByKey($this->getBaseKey(), $this->getValueKey(), $value, $expiration);
	}

	/**
	 *
	 * @param string $value
	 * @param int $expiration
	 * @return boolean
	 */
	public function setIfExist($value, $expiration = null)
	{
		return memcachedPool::replaceByKey($this->getBaseKey(), $this->getValueKey(), $value, $expiration);
	}

	/**
	 *
	 * @param string $value
	 * @return boolean
	 */
	public function set($value)
	{
		return memcachedPool::setByKey($this->getBaseKey(), $this->getValueKey(), $value);
	}

	/**
	 *
	 * @param string $value
	 * @param int $expiration
	 * @return boolean
	 */
	public function setWithExpiration($value, $expiration)
	{
		return memcachedPool::setByKey($this->getBaseKey(), $this->getValueKey(), $value, $expiration);
	}

	/**
	 *
	 * @param int $amount
	 * @return int
	 */
	public function decrement($amount = 1)
	{
		return memcachedPool::decrementByKey($this->getBaseKey(), $this->getValueKey(), $amount);
	}

	/**
	 *
	 * @param int $amount
	 * @return int
	 */
	public function increment($amount = 1)
	{
		return memcachedPool::incrementByKey($this->getBaseKey(), $this->getValueKey(), $amount);
	}
}