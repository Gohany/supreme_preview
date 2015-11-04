<?PHP
class cacheEntry
{
	/**
	 * Cache version.  Should only be an integer, or will break things.
	 */
	const CACHE_VERSION = '1';

	/**
	 * Our key.
	 * @var string
	 */
	public $key;
	/**
	 * Let's retrieved value.
	 * @var string
	 */
	public $value;
	/**
	 * Time to live, in seconds.
	 * @var int
	 */
	public $expiration;
	/**
	 * Engine object we're using.
	 * @var iCacheEngine
	 */
	private $cacheEngine;
	/**
	 * Name of engine we're using.
	 * @var string
	 */
	private $engine;
	public $owner;

	/**
	 *
	 * @param string $key Name of Key
	 * @param mixed $value Default value in entry.
	 * @param int $expiration Time in seconds till expiration.
	 * @param int $engine What cache engine to use.
	 * @throws error
	 */
	public function __construct($key, $value = null, $expiration = 0, $engine = cacheEntryEngine::ENGINE_DEFAULT)
	{
		$this->key = $key;

		if (!is_null($value))
		{
			$this->value = $value;
		}

		$this->expiration = intval($expiration);
		$this->engine = $engine;
	}
	
	public function cacheEngine()
	{
		if (isset($this->cacheEngine))
		{
			return $this->cacheEngine;
		}
		
		switch ($this->engine)
		{
			case cacheEntryEngine::ENGINE_MEMCACHED:
				$this->cacheEngine = new memcachedKey($this->key, false, $this->owner);
				break;
			case cacheEntryEngine::ENGINE_REDIS:
				$this->cacheEngine = new redisKey($this->key, false, $this->owner);
				break;
			default:
				error::addError('Unknown cache engine.');
				throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}
		return $this->cacheEngine;
	}

	public function __destruct()
	{
		if ($this->isLocked())
		{
			$this->unlock();
		}
	}

	public function get()
	{
		if (!$data = $this->cacheEngine()->get())
		{
			$this->value = null;
			return false;
		}

		$this->value = $data;
		return true;
	}

	public function delete()
	{
		$this->value = null;
		return $this->cacheEngine()->delete();
	}

	public function isLocked()
	{
		return $this->cacheEngine()->isLocked();
	}

	public function lock()
	{
		return $this->cacheEngine()->lock();
	}

	public function unlock()
	{
		return $this->cacheEngine()->unlock();
	}

	public function set()
	{
		return $this->cacheEngine()->setWithExpiration($this->value, $this->expiration);
	}

	public function setIfNotExist()
	{
		return $this->cacheEngine()->setIfNotExist($this->value, $this->expiration);
	}

	public function setIfExist()
	{
		return $this->cacheEngine()->setIfExist($this->value, $this->expiration);
	}

	public function increment()
	{
		$value = $this->cacheEngine()->increment();
		if ($value === false)
		{
			return false;
		}

		$this->value = $value;
		return true;
	}

	public function decrement()
	{
		$value = $this->cacheEngine()->decrement();
		if ($value === false)
		{
			return false;
		}

		$this->value = $value;
		return true;
	}
}