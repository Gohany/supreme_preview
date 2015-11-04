<?PHP
/**
 * List of IPs (and ports) of Redis servers to connect to for our pool.
 * Note that changing this value will likely move keys around on servers,
 * resulting in cache-misses for a while.
 */

$redisServerList = array(
	'127.0.0.1:6379'
);
