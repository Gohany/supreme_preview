<?PHP
interface iCacheListEngine
{

	/**
	 * Delete the current value.
	 * @return array
	 */
	public function multiDelete();

	/**
	 * Get the current value.
	 * @return array
	 */
	public function multiGet();

	/**
	 * Set values.
	 * @param array $valueList Keys must match a cacheKey.
	 * @param int $expiration Optional expiration time in seconds
	 * @return array
	 */
	public function multiSet(array $valueList, $expiration = 0);
}