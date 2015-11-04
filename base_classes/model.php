<?php
abstract class model implements ArrayAccess, JsonSerializable
{
	##
	# Properties
	##
	public $fromCache = false;
	public $newInstance = false;
	public $forceSaved = false;
	public $skipDestruct = false;
	public $skipLock = false;
	public $hasChanged;
	public $doNotCache = array();
	public $viewOptions;
	public $gearmanMode = 1;
	public $asyncLevel = 0;
        public $readKey;
        public $readDatabase;
        public $writeKey;
        public $writeDatabase;
        public $database;

	##
	# Async Requests
	##
	public $asyncTrackingLevel = 0;
	public $asyncTrackingFunction = '';
	public $forceAsyncActions = false;

	##
	# Cache
	##
	const CACHE_KEY = null;
	const CACHE_EXPIRATION = SECONDS_IN_DAY;
	const CACHE_PREFIX_MODELCONFIG = 'MODEL_CONFIGS';
	const CACHE_SKIP_LOCK = false;
	const CACHE_PREFIX_MODEL = 'MODEL';
	const DB_CACHE = false;
	const PARENT_MODEL = null;
	const STRING_TYPE = 'modelHash';
	const IS_DB_CACHE_KEY = 'DB_CACHE';
	const CACHE_ENTRY_TYPE = 'cacheInstance';
	const PUBLIC_MODEL = false;
        const BASE_DB = 'base';
        const BASE_KEY = 'db1';
        const BASE_MODEL = false;
        const OWNER_KEY = 'user_id';

	public static $doNotCacheDefault = array(
		'fromCache', 'newInstance', 'forceSaved', 'skipDestruct', 'hasChanged', 'skipLock',
		'doNotCache', 'asyncTrackingLevel', 'asyncTrackingFunction', 'viewOptions',
		'gearmanMode', 'asyncLevel', 'forceAsyncActions'
	);
	public static $COLLECTIONS = array();

	##
	# Misc
	##
	public static $idBuilder;

	/**
	 *
	 * @param array $data
	 * @throws error
	 *
	 * @todo Rewrite this mess
	 */
	public function __construct($data = array())
	{
                
		$cacheKey = self::getCacheKeyStatic($data);
		$owner = self::staticCacheOwner($data);
		if (is_null($cacheKey))
		{
			$this->loadProperties($data);
                        
                        if (property_exists($this, 'account_id') && !empty($this->database) && !empty($this->account_id))
                        {
                                $this->uid = utility::uidFromPair($this->database, $this->account_id);
                        }
                        
			$this->setConfigs();
			return;
		}

		if (static::PUBLIC_MODEL)
		{
			$cache = self::fromCache($cacheKey, $owner);
			if (!$cache)
			{
				$data['newInstance'] = true;
			}

			$this->loadProperties($data, $cache);
                        
                        if (property_exists($this, 'account_id') && !empty($this->database) && !empty($this->account_id))
                        {
                                $this->uid = utility::uidFromPair($this->database, $this->account_id);
                        }
                        
			$this->setConfigs();

			if ($this->fromCache && !empty(static::$COLLECTIONS))
			{
				if (!$this->expandCollections())
				{
					$this->fromCache = false;
					$this->newInstance = true;
					$this->saveHashValue($cacheKey);
				}
			}
			return;
		}
		elseif ((isset($data['skipLock']) && $data['skipLock']) || static::CACHE_SKIP_LOCK)
		{
			$data['skipDestruct'] = true;
			if (isset($data['fromCache']) && $data['fromCache'])
			{
				$this->loadProperties($data);
                                
                                if (property_exists($this, 'account_id') && !empty($this->database) && !empty($this->account_id))
                                {
                                        $this->uid = utility::uidFromPair($this->database, $this->account_id);
                                }
                                
				if (!empty(static::$COLLECTIONS))
				{
					if (!$this->expandCollections())
					{
						$this->fromCache = false;
						$this->newInstance = true;
					}
				}
				$this->setConfigs();
				$this->saveHashValue($cacheKey);
				return;
			}
		}
		elseif (static::hasParent())
		{
			if (!static::lockParent($data))
			{
				error::addError('Failed to lock parent.');
				throw new error(errorCodes::ERROR_INTERNAL_ERROR);
			}
		}
		else
		{
			$this->lock($cacheKey, $owner);
		}

		$cache = self::fromCache($cacheKey, $owner);
		if (!$cache)
		{
			$data['newInstance'] = true;
		}

		$this->loadProperties($data, $cache);
                
                if (property_exists($this, 'account_id') && !empty($this->database) && !empty($this->account_id))
                {
                        $this->uid = utility::uidFromPair($this->database, $this->account_id);
                }
                
		$this->setConfigs();
		if (!$cache)
		{
			$this->saveHashValue($cacheKey);
		}
		if ($this->fromCache && !empty(static::$COLLECTIONS))
		{
			if (!$this->expandCollections())
			{
				$this->fromCache = false;
				$this->newInstance = true;
				$this->saveHashValue($cacheKey);
			}
		}
	}
        
        public function subModel($model, $property = null, $additional = array())
        {
                $startingParams = array(static::PRIMARY_ID => $this->{static::PRIMARY_ID}, static::OWNER_ID => $this->{static::OWNER_ID}, 'database' => $this->database);
                $startingParams = array_merge($startingParams, $additional);
                if (!empty($property) && property_exists(self::getClassName(), $property))
                {
                        $this->{$property} || $this->{$property} = new $model($startingParams);
                        return $this->{$property};
                }
                else
                {
                        return new $model($startingParams);
                }
        }
        
        public function isBaseModel()
        {
                return (bool) (static::BASE_DB == 'base');
        }
        
        public function readInfo()
        {
                if (!empty($this->readKey) || $this->readDatabase)
                {
                        return array($this->readDatabase, $this->readKey);
                }
                
                
                if (!$this->isBaseModel())
                {
                        
                        if (empty($this->database))
                        {
                                $readDatabase = $this->getDatabaseByOwnerKey('read');
                        }
                        else
                        {
                                $readDatabase = $this->database;
                        }
                        $this->readDatabase = static::BASE_DB . '_' . $readDatabase;
                        $this->readKey = dbKeys::keyFromDatabase('read', $readDatabase);
                }
                else
                {
                         $this->readDatabase = self::BASE_DB;
                         $this->readKey = self::BASE_KEY;
                }
                return array($this->readDatabase, $this->readKey);
        }
        
        public function writeInfo()
        {
                if (!empty($this->writeKey) || $this->writeDatabase)
                {
                        return array($this->writeDatabase, $this->writeKey);
                }
                
                
                if (!$this->isBaseModel())
                {
                        $writeDatabase = $this->getDatabaseByOwnerKey('write');
                        $this->writeDatabase = static::BASE_DB . '_' . $writeDatabase;
                        $this->writeKey = dbKeys::keyFromDatabase('write', $writeDatabase);
                }
                else
                {
                         $this->writeDatabase = self::BASE_DB;
                         $this->writeKey = BASE_KEY;
                }
                return array($this->writeDatabase, $this->writeKey);
        }
        
        public function read($key, array $array = array(), $databaseOverite = null, $keyOverwrite = null)
        {
                list($array['database'], $array['dbkey']) = empty($databaseOverite) && empty($keyOverwrite) ? $this->readInfo() : array($databaseOverite, $keyOverwite);
                return dataEngine::read($key, $array);
        }
        
        public function write($key, array $array = array(), $databaseOverite = null, $keyOverwrite = null)
        {
                list($array['database'], $array['dbkey']) = (empty($databaseOverite) && empty($keyOverwrite)) ? $this->readInfo() : array($databaseOverite, $keyOverwite);
                return dataEngine::write($key, $array);
        }
        
        public function getDatabaseByOwnerKey($type)
        {
                if (!isset($this->{static::OWNER_KEY}))
                {
                        error::addError('Owner key not set');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
                }
                $array = dataStore::getArray(static::OWNER_KEY . ':' . $this->{static::OWNER_KEY}, 'DBs');
                return $array[$type];
        }
        
        public function setDBs()
        {
                if (!isset($this->database))
                {
                        error::addError('Database not set.');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
                }
                $array = [
                        'write' => $this->database,
                        'read' => $this->database,
                ];
                dataStore::setArray(static::OWNER_ID . ':' . $this->{static::OWNER_ID}, $array, 'DBs');
        }

	public function __wakeup()
	{
		$this->fromCache = true;
		$cacheKey = $this->getCacheKey();
		if (!is_null($cacheKey))
		{
			$this->saveHashValue($cacheKey);
		}
	}

	public function __destruct()
	{
		if ($this->skipDestruct)
		{
			return;
		}

		$cacheKey = $this->getCacheKey();
		if (is_null($cacheKey))
		{
			return;
		}

		if (error::hasErrors())
		{
			$this->unlock();
			return;
		}

		if (!$this->forceSaved)
		{
			$this->save(false, $cacheKey);
		}

		$this->skipDestruct = true;
		$this->unlock();
	}

	public function __sleep()
	{
		$return = array();

		$doNotCache = array_flip(array_merge(static::$doNotCacheDefault, $this->doNotCache));
		foreach (get_object_vars($this) as $key => $value)
		{
			if (!isset($doNotCache[$key])
				&& $value !== null
				&& !($value instanceof model)
				&& !($value instanceof __PHP_Incomplete_Class)
			)
			{
				$return[] = $key;
			}
		}

		$this->compressCollections();

		return $return;
	}

	public function setAllObjectValues($object, $name, $value)
	{
		$object->{$name} = $value;
		foreach (get_object_vars($this) as $key => $property)
		{
			if (is_object($property))
			{
				$this->setAllObjectValues($property, $name, $value);
			}
		}
	}

	/**
	 * Accepts a string or array of class properties to not cache
	 *
	 * @param string|array $property
	 */
	public function doNotCache($property)
	{
		if (is_array($property))
		{
			$this->doNotCache = $property;
		}
		else
		{
			$this->doNotCache[] = $property;
		}
	}

	public function savePreviousModel($forceDataStore = false)
	{
		$cacheKey = $this->getCacheKey();
		if (is_null($cacheKey))
		{
			return;
		}

		$serialized = serialize($this);

		if ((isset($this->asyncLevel) && $this->asyncLevel >= 3) || ($forceDataStore == true))
		{
			$reflection = new ReflectionClass($this);
			dataStore::appendArrayByKey('previousModels', $cacheKey, array('className' => self::getClassName(), 'file' => $reflection->getFileName(), 'object' => $serialized), 'async');
		}

		if ($this->asyncTrackingLevel >= 2)
		{
			$reflection = new ReflectionClass($this);
			dataStore::appendArrayByKey('previousModels', $cacheKey, array('className' => self::getClassName(), 'file' => $reflection->getFileName(), 'object' => $serialized, 'asyncTrackingFunction' => $this->asyncTrackingFunction, 'cacheKey' => $cacheKey), 'asyncTracking');
		}
		$this->skipDestruct = false;
	}

	public static function getCacheKeyStatic(array $data)
	{
               
		if (isset(static::$CACHE_KEY))
		{
			$cacheKey = self::CACHE_PREFIX_MODEL . cacheEntry::CACHE_VERSION . '_' . self::getClassName() . '_';
			foreach (static::$CACHE_KEY as $key => $CACHE_KEY)
			{
				if (!isset($data[$CACHE_KEY]))
				{
					return null;
				}

				if ($key == 0)
				{
					$cacheKey .= $data[$CACHE_KEY];
				}
				else
				{
					$cacheKey .= '.' . $data[$CACHE_KEY];
				}
			}
			return $cacheKey;
		}
		elseif (isset($data[static::CACHE_KEY]))
		{
			return self::CACHE_PREFIX_MODEL . cacheEntry::CACHE_VERSION . '_' . self::getClassName() . '_' . $data[static::CACHE_KEY];
		}

		return null;
	}

	public function getCacheKey($data = null)
	{
		if (!is_null($data) && is_array($data))
		{
			return self::getCacheKeyStatic($data);
		}

		if (isset(static::$CACHE_KEY))
		{
			$cacheKey = self::CACHE_PREFIX_MODEL . cacheEntry::CACHE_VERSION . '_' . self::getClassName() . '_';
			foreach (static::$CACHE_KEY as $key => $CACHE_KEY)
			{
				if (!isset($this->{$CACHE_KEY}))
				{
					return null;
				}

				if ($key == 0)
				{
					$cacheKey .= $this->{$CACHE_KEY};
				}
				else
				{
					$cacheKey .= '.' . $this->{$CACHE_KEY};
				}
			}
			return $cacheKey;
		}
		elseif (isset($this->{static::CACHE_KEY}))
		{
			return self::CACHE_PREFIX_MODEL . cacheEntry::CACHE_VERSION . '_' . self::getClassName() . '_' . $this->{static::CACHE_KEY};
		}

		return null;
	}

	public static function fromCache($cacheKey, $owner = null)
	{
		if (is_null($cacheKey))
		{
			return false;
		}

		$cacheObject = false;

		$cacheEntry = self::getCacheEntry($cacheKey, $owner);
		if ($cacheEntry->get())
		{
			$cacheObject = unserialize($cacheEntry->value);
		}
		elseif (static::DB_CACHE === true)
		{
			if ($db_cache = dataEngine::read('db_cache', array('shardString' => md5($cacheKey), 'data' => array('cacheKey' => $cacheKey))))
			{
				if (($db_cache['serverTime'] + static::CACHE_EXPIRATION) >= time())
				{
					$cacheObject = unserialize($db_cache['dataString']);
					dataStore::setString($cacheKey, true, self::IS_DB_CACHE_KEY);
				}
			}
		}

		if (!$cacheObject)
		{
			$cacheEntry->delete();
			return false;
		}

		$cacheObject->fromCache = true;

		return $cacheObject;
	}

	protected function getHashValue($cacheKey)
	{
		if (is_null($cacheKey) || $this->skipDestruct)
		{
			return false;
		}
		return dataStore::getString($cacheKey, self::STRING_TYPE);
	}

	protected function saveHashValue($cacheKey)
	{
		if (is_null($cacheKey) || $this->skipDestruct)
		{
			return false;
		}

		dataStore::setString($cacheKey, md5(serialize($this)), self::STRING_TYPE);
		return true;
	}

	public static function environment($param)
	{
		if ($param !== false && !is_numeric($param) && is_string($param))
		{
			return $param;
		}
		else
		{
			return ENVIRONMENT;
		}
	}

	public static function setConfigs()
	{
		$className = self::getClassName();

		if (!isset($className::$_CONFIGS) || !is_array($className::$_CONFIGS) || empty($className::$_CONFIGS))
		{
			return false;
		}

		if (!$cacheValues = self::loadConfigs($className))
		{
			return false;
		}

		foreach ($className::$_CONFIGS as $property)
		{
			if (!isset($cacheValues[$property]))
			{
				error::addError('Missing Config setting ' . utility::addQuotes($property) . '.');
				throw new error(errorCodes::ERROR_INTERNAL_ERROR);
			}

			$className::${$property} = $cacheValues[$property];
		}
		return true;
	}

	public static function loadConfigs($className)
	{
		$cacheKey = self::CACHE_PREFIX_MODELCONFIG . cacheEntry::CACHE_VERSION . '_' . $className;

		//Get environment of class from name
		if (!$environment = self::getEnvironmentFromClassName($className))
		{
			return false;
		}

		$loadFromFile = false;

		//Check cache
		$configCacheEntry = self::getCacheEntry($cacheKey);
		if (!is_null($configCacheEntry->value) || $configCacheEntry->get())
		{
			$cacheValues = unserialize($configCacheEntry->value);
			foreach ($className::$_CONFIGS as $property)
			{
				if (!isset($cacheValues[$property]))
				{
					$loadFromFile = true;
					break;
				}
			}

			if (!$loadFromFile)
			{
				return $cacheValues;
			}
		}
		elseif ($values = dataEngine::read($environment . 'Config', array('data' => array('cacheKey' => $cacheKey))))
		{
			//Load from DB
			$cacheValues = unserialize($values['dataString']);
			foreach ($className::$_CONFIGS as $property)
			{
				if (!isset($cacheValues[$property]))
				{
					$loadFromFile = true;
					break;
				}
			}
		}
		else
		{
			$loadFromFile = true;
		}

		if ($loadFromFile)
		{
			//Load from File
			$cacheValues = array();
			foreach (glob($_SERVER['_HTDOCS_'] . '/environments/' . $environment . '/settings/' . $className . '/*.php') as $filename)
			{
				$property = basename($filename, '.php');
				require $filename;

				if (!isset(${$property}))
				{
					error::addError('Property ' . utility::addQuotes($property) . ' not found in file.');
					throw new error(errorCodes::ERROR_INTERNAL_ERROR);
				}

				$cacheValues[$property] = ${$property};
			}

			//Save to DB
			if (!dataEngine::write($environment . 'Config', array('action' => 'insert', 'data' => array('cacheKey' => $cacheKey, 'dataString' => serialize($cacheValues)))))
			{
				error::addError('Failed to write settings to DB.');
				throw new error(errorCodes::ERROR_INTERNAL_ERROR);
			}
		}

		//Save to cache
		self::saveValueToCache($cacheKey, serialize($cacheValues), static::CACHE_EXPIRATION);

		return $cacheValues;
	}

	public function loadSettings($function, $environment)
	{
		require_once $_SERVER['_HTDOCS_'] . '/environments/' . $environment . '/models/' . $function . '.php';
		$class = $environment . ucfirst($function) . 'Model';

		if (!isset($class::$_CONFIGS))
		{
			return false;
		}

		return $this->listConfigs($class::$_CONFIGS, $class, $function);
	}

	public function listConfigs($configList, $className, $propertyName)
	{
		if (empty($configList) || !is_array($configList))
		{
			return false;
		}

		if ($cacheValues = self::loadConfigs($className))
		{
			foreach ($configList as $config)
			{
				$this->{$propertyName}[$config] = $cacheValues[$config];
			}
			return true;
		}
		return false;
	}

	public function loadProperties(array $data, $cachedValueList = false)
	{
		$class = self::getClassName();
		foreach ($data as $key => $value)
		{
			if (property_exists($class, $key))
			{
				$this->{$key} = $value;
			}
		}

		if ($cachedValueList)
		{
			foreach ($cachedValueList as $key => $value)
			{
				if (property_exists($class, $key) && isset($cachedValueList->$key))
				{
					$this->$key = $cachedValueList->$key;
				}
			}
			$cachedValueList->skipDestruct = true;
		}
	}

	public function deleteCache()
	{
		$cacheKey = $this->getCacheKey();

		if (is_null($cacheKey))
		{
			return;
		}

		if (static::DB_CACHE)
		{
			dataEngine::write('db_cache', array('action' => 'delete', 'shardString' => md5($cacheKey), 'data' => array('cacheKey' => $cacheKey)));
		}

		$cacheEntry = self::getCacheEntry($cacheKey);
		if (!$cacheEntry->delete())
		{
			return false;
		}

		$this->skipDestruct = true;

		return true;
	}

	/**
	 * Has our model changed since loading?
	 * @param string $cacheKey
	 * @return boolean Success
	 */
	public function hasChanged($cacheKey)
	{
		if (isset($this->hasChanged))
		{
			return $this->hasChanged;
		}

		if ($this->newInstance)
		{
			$this->hasChanged = true;
		}
		else
		{
			$hashValue = $this->getHashValue($cacheKey);
			if ($hashValue === false)
			{
				$this->hasChanged = false;
			}
			else
			{
				$newHashValue = md5(serialize($this));
				if ($newHashValue != $hashValue)
				{
					$this->hasChanged = true;
				}
				else
				{
					$this->hasChanged = false;
				}
			}
		}
		return $this->hasChanged;
	}

	public function save($forceSave = false, $cacheKey = null)
	{
		if ($forceSave === true && is_null($cacheKey))
		{
			$cacheKey = $this->getCacheKey();
		}

		if (is_null($cacheKey))
		{
			return;
		}

		$fromDBCache = dataStore::getString($cacheKey, self::IS_DB_CACHE_KEY);
		$hasChanged = $this->hasChanged($cacheKey);

		if ($forceSave)
		{
			$this->forceSaved = true;
		}
		elseif ($fromDBCache && !$hasChanged)
		{
			return;
		}

		$serialized = serialize($this);

		//Don't save to DB cache if we received the data from DB cache, unless it's actually changed or we want to force save.
		if (static::DB_CACHE === true && (!$fromDBCache || ($fromDBCache && ($hasChanged || $forceSave))))
		{
			if (!dataEngine::write('db_cache', array(
					'action' => 'insert',
					'shardString' => md5($cacheKey),
					'data' => array(
						'cacheKey' => $cacheKey,
						'dataString' => $serialized,
						'serverTime' => time()
					)
				))
			)
			{
				error::addError('Failed to write db_cache.');
				throw new error(errorCodes::ERROR_INTERNAL_ERROR);
			}
		}

		if (!$hasChanged && !$forceSave)
		{
			return true;
		}

		//Save to cache
		return self::saveValueToCache($cacheKey, $serialized, static::CACHE_EXPIRATION, $this->cacheOwner());
	}

	public function isCorrectID($id, $value)
	{
		//Check if this is a value being checked
                if (property_exists($this, $id) && $this->{$id} == $value)
		{
			return true;
		}
                return false;
	}

	public static function isComplete($object, $exceptions = array())
	{
		foreach (get_class_vars(self::getClassName()) as $property => $defaultValue)
		{
			if (in_array($property, array_merge(static::$doNotCacheDefault, $object->doNotCache))
			)
			{
				continue;
			}

			if ((!isset($object->{$property}) || $object->{$property} === $defaultValue)
				&& !in_array($property, $exceptions)
			)
			{
				return false;
			}
		}
		return true;
	}

	/**
	 * Skip cache lock?
	 * @return boolean
	 */
	private function useLock()
	{
		return (!static::CACHE_SKIP_LOCK && !$this->skipLock);
	}
        
	public function cacheOwner()
	{
		if (!empty(static::$cacheOwner) && isset(static::$cacheOwner['type']) && isset(static::$cacheOwner['property']) && isset($this->{static::$cacheOwner['property']}))
		{
			$owner['type'] = static::$cacheOwner['type'];
			$owner['property'] = $this->{static::$cacheOwner['property']};
		}
		else
		{
			$owner = null;
		}
		return $owner;
	}

	public static function staticCacheOwner($data)
	{
		if (!empty(static::$cacheOwner) && isset(static::$cacheOwner['type']) && isset(static::$cacheOwner['property']) && isset($data[static::$cacheOwner['property']]))
		{
			$owner['type'] = static::$cacheOwner['type'];
			$owner['property'] = $data[static::$cacheOwner['property']];
		}
		else
		{
			$owner = null;
		}
		return $owner;
	}

	/**
	 * Lock our cacheEntry;
	 * @return boolean Success
	 */
	public function lock($cacheKey, $owner = null)
	{
		if (!$this->useLock())
		{
			return true;
		}

		$cacheEntry = self::getCacheEntry($cacheKey, $owner);
		return $cacheEntry->lock();
	}

	/**
	 * Unlock our cache entry.
	 * @return boolean Success boolean
	 */
	public function unlock()
	{
		if (!$this->useLock())
		{
			return true;
		}

		if (!static::hasParent())
		{
			return true;
		}

		$cacheKey = $this->getCacheKey();
		if (is_null($cacheKey))
		{
			return false;
		}

		$cacheEntry = self::getCacheEntry($cacheKey, $this->cacheOwner());
		if (!$cacheEntry->isLocked())
		{
			return true;
		}
		return $cacheEntry->unlock();
	}

	/**
	 * Get or Create a cacheEntry object for a cacheKey
	 * @param string $cacheKey
	 * @return \cacheEntry
	 */
	private static function getCacheEntry($cacheKey, $owner = null)
	{
		if (!$cacheEntry = dataStore::getObject($cacheKey, static::CACHE_ENTRY_TYPE))
		{
			#$cacheEntry = new cacheEntry($cacheKey, null, 0, iCacheEntryEngine::ENGINE_MEMCACHED);
			$cacheEntry = new cacheEntry($cacheKey);
			$cacheEntry->owner = $owner;
			dataStore::setObject($cacheEntry->key, $cacheEntry, static::CACHE_ENTRY_TYPE);
		}

		return $cacheEntry;
	}

	/**
	 * Returns the late-static class that calls this.
	 * @return string
	 */
	public static function getClassName()
	{
		return get_called_class();
	}

	/**
	 * Get the environment of a class from the start of it's name.
	 * @param string $className
	 * @return string|boolean
	 */
	public static function getEnvironmentFromClassName($className)
	{
		//Check DB
		if ($className === 'srpModel')
		{
			return 'base';
		}
                if (substr($className, 0, 4) == 'base')
                {
                        return 'base';
                }
                elseif (substr($className, 0, 9) == 'broadcast')
                {
                        return 'broadcast';
                }
                elseif (substr($className, 0, 8) == 'frontend')
                {
                        return 'frontend';
                }
                elseif (substr($className, 0, 3) == 'hrs')
                {
                        return 'hrs';
                }

		return false;
	}

	/**
	 *
	 * @param string $cacheKey
	 * @param string $value
	 * @param int $expiration Time to live in seconds
	 * @return type
	 */
	private static function saveValueToCache($cacheKey, $value, $expiration, $owner = null)
	{
		$cacheEntry = self::getCacheEntry($cacheKey, $owner);
		$cacheEntry->value = $value;
		$cacheEntry->expiration = $expiration;
		return $cacheEntry->set();
	}

	/**
	 * Does this class have a parent?
	 *
	 * @return boolean
	 */
	protected static function hasParent()
	{
		$parent = static::PARENT_MODEL;
		if (empty($parent))
		{
			return false;
		}

		return true;
	}

	/**
	 * Statically lock a class's logical (not inherited) parent.
	 *
	 *
	 * @param array $data
	 * @return boolean
	 */
	protected static function lockParent(array $data)
	{
		if (!static::hasParent())
		{
			return true;
		}

		if (!self::includeModel(static::PARENT_MODEL))
		{
			return false;
		}

		$parentLockData = static::getParentLockCacheKeyData($data);
                
		return forward_static_call(array(static::PARENT_MODEL, 'lockFromChild'), $parentLockData);
	}

	/**
	 * Lock a class statically to avoid loading it's data.
	 *
	 * @param array $data
	 * @return boolean
	 */
	public static function lockFromChild(array $data)
	{
		$cacheKey = self::getCacheKeyStatic($data);
		if (is_null($cacheKey))
		{
			error::addError('Could not get cache key');
			return false;
		}

		$owner = static::staticCacheOwner($data);
		//Are we already locked?
		$cacheEntry = self::getCacheEntry($cacheKey, $owner);
		if ($cacheEntry->isLocked())
		{
			return true;
		}

		//Lock our parent if we have one before we lock ourselves
		if (static::hasParent())
		{
			if (!self::lockParent($data))
			{
				error::addError('Could not lock parent');
				return false;
			}
			return true;
		}

		return $cacheEntry->lock();
	}

	/**
	 * Automatically include a model based on the class name.
	 *
	 * This could potentially be thrown in an auto-loader, but that may reduce
	 * the legibility of code that made use of it.
	 *
	 * @param string $modelName
	 * @return boolean
	 */
	protected static function includeModel($modelName)
	{
		if (class_exists($modelName, false))
		{
			return true;
		}

		$environment = self::getEnvironmentFromClassName($modelName);
		if ($environment === false)
		{
			return false;
		}

		$filePath = $_SERVER['_HTDOCS_'] . '/environments/' . $environment . '/models/' . lcfirst(substr($modelName, strlen($environment), -5)) . '.php';
		if (!is_file($filePath))
		{
			return false;
		}
		require_once $filePath;

		return true;
	}

	/**
	 * Override this method if you need to give any extra data to have all the values
	 * for a parent's cache key.
	 *
	 * @param array $data
	 * @return array
	 */
	protected static function getParentLockCacheKeyData(array $data)
	{
		return $data;
	}

	/**
	 * Get list of collections from the current model that are not empty, and include
	 * the model of any compressed keys before we uncompress them.
	 *
	 * @return boolean|array
	 * @throws error
	 */
	private function getValidCollections()
	{
		if (empty(static::$COLLECTIONS) || !is_array(static::$COLLECTIONS))
		{
			return false;
		}

		$collectionList = array();
		$class = static::getClassName();
		foreach (static::$COLLECTIONS as $collectionName)
		{
			//Validate and include files
			if (!property_exists($class, $collectionName))
			{
				error::addError('Invalid collection configuartion; no such collection "' . $collectionName . '".');
				throw new error(errorCodes::ERROR_INTERNAL_ERROR);
			}

			if (empty($this->$collectionName) || !is_array($this->$collectionName))
			{
				continue;
			}

			foreach ($this->$collectionName as $cacheKey)
			{
				//Check if is a cachekey, or has been already expanded to an object.  If it's expanded, it's already included.
				if (!is_string($cacheKey))
				{
					continue;
				}

				$modelName = self::getModelNameFromCacheKey($cacheKey);
				if (!self::includeModel($modelName))
				{
					error::addError('Failed to include model "' . $modelName . '".');
					throw new error(errorCodes::ERROR_INTERNAL_ERROR);
				}

				if (!class_exists($modelName, false))
				{
					error::addError('Failed to have model "' . $modelName . '".');
					throw new error(errorCodes::ERROR_INTERNAL_ERROR);
				}
			}

			$collectionList[] = $collectionName;
		}

		return $collectionList;
	}

	/**
	 * If we have any models in our collections, compress them into cacheKeys.
	 */
	private function compressCollections()
	{
		$collectionList = $this->getValidCollections();

		if (!is_array($collectionList) || count($collectionList) === 0)
		{
			return;
		}

		foreach ($collectionList as $collectionName)
		{
			foreach ($this->$collectionName as &$value)
			{
				if (is_object($value))
				{
					$value = $value->getCacheKey();
				}
			}
		}
	}

	/**
	 * If we have any cacheKeys in our collections, expand them into models.
	 */
	private function expandCollections()
	{
		$collectionList = $this->getValidCollections();

		if (!is_array($collectionList) || count($collectionList) === 0)
		{
			return true;
		}

		$success = true;
		foreach ($collectionList as $collectionName)
		{
			if (empty($this->$collectionName))
			{
				continue;
			}

			$cacheKeyList = array();
			foreach ($this->$collectionName as $key => $value)
			{
				if ($value === null)
				{
					$success = false;
					break 2;
				}
				elseif (is_string($value))
				{
					$cacheKeyList[$key] = $value;
				}
			}
			if (count($cacheKeyList) === 0)
			{
				$success = false;
				break;
			}

			$cacheEntryList = new cacheList($cacheKeyList);
			$cacheEntryList->setOwner($this->cacheOwner());
			$cacheEntryList->multiGet();

			$collectionPreExpansion = array_flip($cacheKeyList);

			foreach ($cacheEntryList->valueList as $cacheKey => $value)
			{
				$modelName = self::getModelNameFromCacheKey($cacheKey);

				if (is_string($value))
				{
					$value = @unserialize($value);
				}

				if (!is_object($value))
				{
					$model = new $modelName(self::getValueArrayFromCacheKey($cacheKey));
					$model->skipDestruct = false;
				}
				else
				{
					$value->fromCache = true;
					$model = new $modelName(get_object_vars($value));
					$value->skipDestruct = true;
				}

				$this->{$collectionName}[$collectionPreExpansion[$cacheKey]] = $model;
			}
		}

		if (!$success)
		{
			foreach ($collectionList as $collectionName)
			{
				$this->$collectionName = array();
			}

			return false;
		}

		return true;
	}

	/**
	 * Get a model name from a model cacheKey string.
	 *
	 * @param string $cacheKey
	 * @return string
	 * @throws error
	 */
	private static function getModelNameFromCacheKey($cacheKey)
	{
		$keyList = explode('_', $cacheKey, 3);
		if (count($keyList) < 3 || $keyList[0] !== self::CACHE_PREFIX_MODEL . cacheEntry::CACHE_VERSION)
		{
			error::addError('Is this cacheKey of a model? "' . $cacheKey . '"');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		return $keyList[1];
	}

	/**
	 * Breaks apart the values at the end of a cacheKey, and creates an array of information
	 * which can be used to load a child model from the database.
	 *
	 * @param string $cacheKey
	 * @return array
	 * @throws error
	 */
	private static function getValueArrayFromCacheKey($cacheKey)
	{
		$keyList = explode('_', $cacheKey, 3);
		if (count($keyList) < 3 || $keyList[0] !== self::CACHE_PREFIX_MODEL . cacheEntry::CACHE_VERSION)
		{
			error::addError('Is this cacheKey of a model? "' . $cacheKey . '"');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		$modelName = $keyList[1];
                
		$data = array();
		if (isset($modelName::$CACHE_KEY))
		{
			$keyString = $keyList[2];
			foreach ($modelName::$CACHE_KEY as $key => $valueName)
			{
				$matchList = null;
				if (substr($valueName, -3) === '_id')
				{
					$matchCount = preg_match('/[\d\w]+\.[\d\w]+/u', $keyString, $matchList);
				}
				else
				{
					$matchCount = preg_match('/^[\d\w]+/u', $keyString, $matchList);
				}
				if ($matchCount == 0)
				{
					error::addError('Did not match value.');
					throw new error(errorCodes::ERROR_INTERNAL_ERROR);
				}
				$data[$valueName] = $matchList[0];
				$keyString = substr($keyString, strlen($matchList[0]) + 1);
			}
		}
		elseif (defined($modelName . '::CACHE_KEY'))
		{
			$keyName = constant($modelName . '::CACHE_KEY');
			$data[$keyName] = $keyList[2];
		}
		else
		{
			error::addError('No cache key?');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		//We have already locked by our parents, no need to relock
		$data['skipLock'] = true;

		return $data;
	}

	public static function ensureSettingsAreLoaded()
	{
		if (!is_array(static::$_CONFIGS) || count(static::$_CONFIGS) == 0)
		{
			return;
		}
		foreach (static::$_CONFIGS as $config)
		{
			if (static::${$config} === null)
			{
				static::setConfigs();
				return;
			}
		}
	}

	public function offsetSet($offset, $value)
	{
		if (empty($offset))
		{
			$this->{$value} = $value;
		}
		else
		{
			$this->{$offset} = $value;
		}
	}

	public function offsetExists($offset)
	{
		return isset($this->{$offset});
	}

	public function offsetUnset($offset)
	{
		unset($this->{$offset});
	}

	public function offsetGet($offset)
	{
		return isset($this->{$offset}) ? $this->{$offset} : null;
	}

	public function jsonSerialize()
	{
		$return = array();
		foreach ($this->__sleep() as $key)
		{
			$return[$key] = &$this->$key;
		}
		return $return;
	}

	public function logEvent($category, $entityType, $entityId, $key, $value, $data = array())
	{
		$model = self::getClassName();
		$environment = self::getEnvironmentFromClassName($model);
		eventLog::log($environment, $model, $category, $entityType, $entityId, $key, $value, $data);
	}
        // TODO
        // make sure generic
        public function logThisRequest($call)
	{
                
                $data['admin_id'] = $call->X_id;
                $data['adminEnvironment'] = $call->X_environment;
                $data['environment'] = $call->environment;
                $data['requester'] = $call->requester;
                $data['controller'] = $call->getClassName();
                $data['action'] = $call->action;
                $data['queryString'] = http_build_query($call->input);
                
                if (!$this->write('adminLog', array(
                                'action' => 'insert',
                                'data' => $data
                        )))
                {
                        error::addError('Failed to log this request.');
                        throw new error(errorCodes::ERROR_INTERNAL_ERROR);
                }
		
	}
}