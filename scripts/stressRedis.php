<?php

if (!isset($_SERVER['_HTDOCS_']))
{
	$_SERVER['_HTDOCS_'] = getcwd();
}

require_once $_SERVER['_HTDOCS_'] . '/base_include.php';


$value32 = utility::getRandomBytes(32);
$value1024 = utility::getRandomBytes(1024);
$value10240 = utility::getRandomBytes(10240);
$value102400 = utility::getRandomBytes(102400);
$start = time();
$c=0;

while ($start + 30 >= time())
{
	$cacheKey = crc32(microtime(true));
	$cacheEntry = new cacheEntry($cacheKey);
	#$cacheEntry->value = $value;
	#$cacheEntry->expiration = $expiration;
	$cacheEntry->get();
	$c++;
}

print "Did ".$c." saves in 30 seconds";