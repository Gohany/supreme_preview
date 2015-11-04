<?php

require_once $_SERVER['_HTDOCS_'] . '/base_include.php';
$ip = geoip::getClientIP();

echo '##' . PHP_EOL;
echo '# Your ip is ' . $ip . PHP_EOL;
echo '##' . PHP_EOL;


