<?PHP
if (!isset($_SERVER['_HTDOCS_']))
{
	$_SERVER['_HTDOCS_'] = getcwd();
}

require_once $_SERVER['_HTDOCS_'] . '/base_include.php';

if (redisPool::flushDB(redisKey::DATABASE_CACHE_DATA))
{
	print "Success" . PHP_EOL;
}
else
{
	print "Fail" . PHP_EOL;
}