<?php
$requiredModules = array(
	'gearman' => 'Gearman Job Server',
	'geoip' => 'GeoIP',
	'gmp' => 'GMP Big Number Library',
	'hash' => 'Hash',
	'igbinary' => 'IGBinary Serializer',
	'mbstring' => 'Multi-Byte String',
	'memcached' => 'MemcacheD',
	'mysqli' => 'Mysql Improved Library',
	'openssl' => 'OpenSSL',
	'pcre' => 'Perl Regular Expressions',
	'redis' => 'Redis',
	'SimpleXML' => 'Simple XML Library',
	'xml' => 'XML',
	'zmq' => 'ZeroMQ'
);

foreach ($requiredModules as $name => $label)
{
	echo (extension_loaded($name)) ? 'FOUND ' : 'MISSING ';
	echo ' required module "' . $label . '"' . PHP_EOL;
}
