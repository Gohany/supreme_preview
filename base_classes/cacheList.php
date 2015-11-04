<?PHP
class cacheList
{
	/**
	 * List of keys
	 *
	 * @var array
	 */
	public $cacheKeyList;
	/**
	 * A hashmap based on keys found in $cacheEntryList
	 *
	 * @var array
	 */
	public $valueList;
	/**
	 * Engine object we're using.
	 *
	 * @var iCacheListEngine
	 */
	private $cacheListEngine;
	/**
	 * Name of engine we're using.
	 *
	 * @var string
	 */
	private $engine;
	/**
	 * Ownership
	 * @var string
	 */
	private $owner;

	/**
	 *
	 * @param string $key Name of Key
	 * @param mixed $valueList Default value in entry.
	 * @param int $expiration Time in seconds till expiration.
	 * @param int $engine What cache engine to use.
	 * @throws error
	 */
	public function __construct($cacheKeyList, $valueList = null, $expiration = 0, $engine = cacheEntryEngine::ENGINE_DEFAULT)
	{
		$this->cacheKeyList = $cacheKeyList;

		if (!is_null($valueList))
		{
			$this->valueList = $valueList;
		}

		$this->expiration = intval($expiration);
		$this->engine = $engine;
	}

	public function cacheListEngine()
	{
		if (isset($this->cacheListEngine))
		{
			return $this->cacheListEngine;
		}

		switch ($this->engine)
		{
			case cacheEntryEngine::ENGINE_REDIS:
				$this->cacheListEngine = new redisPool($this->cacheKeyList, $this->owner);
				break;
			case cacheEntryEngine::ENGINE_MEMCACHED:
				$this->cacheListEngine = new memcachedPool($this->cacheKeyList, $this->owner);
				break;
			default:
				error::addError('Unknown cache engine.');
				throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		return $this->cacheListEngine;
	}

	public function multiGet()
	{
		$this->valueList = $this->cacheListEngine()->multiGet();
	}

	public function multiDelete()
	{
		$this->valueList = null;
		return $this->cacheListEngine()->multiDelete();
	}

	public function multiSet()
	{
		return $this->cacheListEngine()->multiSet($this->valueList, $this->expiration);
	}

	public function setOwner($owner)
	{
		$this->owner = $owner;
	}

	public function getOwner()
	{
		return $this->owner;
	}
}