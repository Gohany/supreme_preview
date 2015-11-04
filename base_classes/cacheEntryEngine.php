<?PHP
interface cacheEntryEngine
{
	/**
	 * Use Redis for cache.
	 */
	const ENGINE_REDIS = 1;

	/**
	 * Use Memcached for cache.
	 */
	const ENGINE_MEMCACHED = 2;

	/**
	 * Default engine
	 */
	const ENGINE_DEFAULT = self::ENGINE_REDIS;

	/**
	 * Lock lifetime in seconds.
	 */
	const LOCK_LIFETIME = 5;

	/**
	 * Gets a lock on our cache key.
	 * @return boolean
	 */
	public function lock();

	/**
	 * Unlock our cache key if we're currently locked
	 * @return boolean
	 */
	public function unlock();

	/**
	 * Are we currently locked?
	 * @return boolean
	 */
	public function isLocked();

	/**
	 * Delete the current value.
	 * @return boolean
	 */
	public function delete();

	/**
	 * Get the current value.
	 * @return string|boolean
	 */
	public function get();

	/**
	 * Set a value, but only if it doesn't already exist.
	 * @param string $value
	 * @param int $expiration
	 * @return boolean
	 */
	public function setIfNotExist($value, $expiration = null);

	/**
	 * Set a value, but only if it exists already.
	 * @param string $value
	 * @param int $expiration
	 * @return boolean
	 */
	public function setIfExist($value, $expiration = null);

	/**
	 * Set a value with no expiration.
	 * @param string $value
	 * @return boolean
	 */
	public function set($value);

	/**
	 * Set a value with an expiration.
	 * @param string $value
	 * @param int $expiration
	 * @return boolean
	 */
	public function setWithExpiration($value, $expiration);

	/**
	 * Increment our value by $amount
	 * @return boolean
	 */
	public function increment($amount = 1);

	/**
	 * Decrement our value by $amount
	 * @return boolean
	 */
	public function decrement($amount = 1);
}