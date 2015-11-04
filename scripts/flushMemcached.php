<?PHP
require_once $_SERVER['_HTDOCS_'] . '/base_include.php';

if (memcachedPool::flushAll())
{
	print "Success";
}
else
{
	print "Fail";
}