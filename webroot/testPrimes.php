<?php

if (!isset($_SERVER['_HTDOCS_']))
{
	$_SERVER['_HTDOCS_'] = realpath(dirname(__FILE__) . '/..');
}

require_once $_SERVER['_HTDOCS_'] . '/base_include.php';

print utility::generatePrime().PHP_EOL;