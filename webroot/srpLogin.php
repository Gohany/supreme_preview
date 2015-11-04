<?php

if (!isset($_SERVER['_HTDOCS_']))
{
	$_SERVER['_HTDOCS_'] = realpath(dirname(__FILE__) . '/..');
}

require_once $_SERVER['_HTDOCS_'] . '/base_include.php';

$url = 'http://127.0.0.1/c/base/authenticate/email/gohany@gmail.com';
$client = srpModel::begin('gohany@gmail.com');

$params = array ('A' => $client->A);
$response = rest::POST($url, $params);
print "<pre>";

utility::printHexDump(strval($response));
print PHP_EOL;

$object = json_decode(substr($response, 3));
var_dump($object);

#require_once $_SERVER['_HTDOCS_'] . '/environments/base/models/user.php';
#$user = baseUserModel::fromEmail("gohany@gmail.com");
#$user->srpfromPreAuth($object->body->srp->A);

require_once $_SERVER['_HTDOCS_'] . '/base_classes/utility/password.php';
$password = 'test';
$passwordHash = utility::hashPassword(passwordModel::PASSWORD_SALT . $password, $object->body->srp->salt2);

$client->password = $passwordHash;
$client->B = $object->body->srp->B;
$client->salt = $object->body->srp->B;
$client->salt_resource = gmp_init($object->body->srp->salt, 16);
$client->B_resource = gmp_init($client->B, 16);
$client->k();
$client->x();
$client->u();
$client->v();
$client->clientSessionKey();
$client->serverSessionKey = $client->clientSessionKey;
$client->serverSessionKey_resource = gmp_init($client->clientSessionKey, 16);
$client->clientProof();
$client->serverProof();

#$finalSRP = $user->srpfromReply();

$url = 'http://127.0.0.1/c/base/session/email/gohany@gmail.com';

$params = array ('proof' => $client->clientProof);
$response = rest::POST($url, $params);

utility::printHexDump(strval($response));
print PHP_EOL;

$object = json_decode(substr($response, 3));
var_dump($object);

print "Client proofs:" . PHP_EOL;
print $client->clientProof . PHP_EOL;
#print $finalSRP->clientProof. PHP_EOL;
print "Server proofs:" . PHP_EOL;
print $client->serverProof . PHP_EOL;
#print $finalSRP->serverProof. PHP_EOL;

print "SESSION KEY (hidden)" . PHP_EOL;
print $client->clientSessionKey . PHP_EOL;
#print $finalSRP->serverSessionKey . PHP_EOL;
print "</pre>";