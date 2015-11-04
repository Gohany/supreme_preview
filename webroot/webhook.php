<?php
//Validation.
$username = 'SendGrindWebhook';
$password = 'pppaaassssswwoorrrddd';
var_dump($_SERVER);
if(!isset($_SERVER['PHP_AUTH_USER']))
{
	http_response_code(401);
	ob_end_clean();
	die();
}

if($_SERVER['PHP_AUTH_USER'] !== $username || $_SERVER['PHP_AUTH_PW'] !== $password)
{
	http_response_code(401);
	ob_end_clean();
	die();
}

if (!isset($_SERVER['_HTDOCS_']))
{
	$_SERVER['_HTDOCS_'] = realpath(dirname(__FILE__) . '/..');
}

require_once $_SERVER['_HTDOCS_'] . '/base_include.php';

class storeWebhook
{
	const LOG_KEY = 'SendGrindWebhook';
	
	public function addToCache($data)
	{
		$redis = RedisPool::getRedisKey(self::LOG_KEY);
		$redis->zAdd(time(), $data);
	}
}

$data = file_get_contents("php://input");

$store = new storeWebhook();
$store->addToCache($data);

http_response_code(200);
ob_end_clean();
die();