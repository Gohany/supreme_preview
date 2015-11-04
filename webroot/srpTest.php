<?php

if (!isset($_SERVER['_HTDOCS_']))
{
	$_SERVER['_HTDOCS_'] = realpath(dirname(__FILE__) . '/..');
}

require_once $_SERVER['_HTDOCS_'] . '/base_include.php';

//$url = 'http://127.0.0.1/c/base/authenticate/email/gohany@gmail.com';
//$client = srpModel::begin('gohany@gmail.com');
//
//$params = array ('A' => "35e237af56409f058f7a4980c4c08e0ea41fa931e65fff1076fd6f30a50954e52676a24e486c20c14aa2e8b3eafdd0894dc00eb0c9f352b799ae886d68170264ca6b3fe88ecc212b0bbb11947a6d1eb8266ac0bb99a47d1f6030749e54e52182ffe94de3ff5ee32220e4337d228ceba7e16595d7ba8e85ed968aeea128a12b54");
//$response = rest::POST($url, $params);
//print "<pre>";
//
//utility::printHexDump(strval($response));
//print PHP_EOL;
//
//$object = json_decode(substr($response, 3));
//var_dump($object);

#require_once $_SERVER['_HTDOCS_'] . '/environments/base/models/user.php';
#$user = baseUserModel::fromEmail("gohany@gmail.com");
#$user->srpfromPreAuth($object->body->srp->A);

require_once $_SERVER['_HTDOCS_'] . '/base_classes/utility/password.php';
$password = 'test';
#print "Just before hash: ".passwordModel::PASSWORD_SALT . $password;
$passwordHash = utility::hashPassword(passwordModel::PASSWORD_SALT . $password, 'D9dBbYustK1glhaCMolYDG');

#var_dump(hash('sha256', "WrAq&paHAc_e7aRu-utE=rePr72h*nUs-!d@BrekaS*ajax!faDAheC?3eceK!?utestD9dBbYustK1glhaCMolYDG"));

$b = "6eb6790ba56a83f6809f9791adf68487f68f05d1a72273d2f2a95bfb3e6662f7a0d3e8fe74e3062d8b7492ff324a1f82a80eb389224dfdc7ba29637be19fdba8faa24d1828e0309b5197fe65ca8602dbe54c9320d7392d0c3d11e7943c56e5dbe0f202c49eac39b3a9f781fdc3147e8df935aebc05c82eaf8cf4f847d91e6836";
$a = "a630291fba01a4f687c3e74a764348fec816697265ff0f3281c8d5e26589781a2ce7c10972c3aae36dd96343d81826d1fb79809059db6a0a0dc3f188ee5a57f95d0506d33d2cb174e626dbe2e36a5af263eb5d8b6101fd0c3f0b95b226231110fe3edf7730bd5f60696baadb7d589cb995e1d585c0488666f80112af1fb7a84a";
$passwordSalt = "94257a32961e08e93543dd9413d91d69183443c945588f3d53c3cd8eb20b2d1e95147dc75db2126ab7379389637ad0b4345f3c2fbbc5c8a6afbfe22e49cdaab0f44daf3a73b880378e5a3ae5c19eb54c91ee646c46ad5451499d8b83a8be02e1ec715b856024c54572e2fc4221156c0ee6a38a3faca0bd60a1f3f6f12a09c4d38ab314f46d189d752b4952825ed9257caa0cfc8792a37b0acad04f6436bbf8c8cdb59a5ce603b4ee8d0f3ee5bd25c34b9c4a93d23e56c9f8804871d9fe21267fa3caf82a06fc15ad7e31676cb59e226ff4dd98a19c715e3235fb8986e040e1124e53812d91c0fbb07f056f361e4b0f98ba82666a6e583f950c628a0f39b03ff1";
$id = 'gohany@gmail.com';
$construct = array(
        'values' => array('a' => $a, 'b' => $b, 'salt' => $passwordSalt, 'password' => $passwordHash, 'id' => $id),
        'generate' => array(
                'k',
                'x',
                'v',
                'A_from_a',
                'B_from_b',
                'u',
                'clientSessionKey',
                'clientProofFromClient',
        ),
);

$srp = new srpModel($construct);
print "<pre>";
var_dump($srp);
print "</pre>";
exit;

$client->password = $passwordHash;
$client->B = "6eb6790ba56a83f6809f9791adf68487f68f05d1a72273d2f2a95bfb3e6662f7a0d3e8fe74e3062d8b7492ff324a1f82a80eb389224dfdc7ba29637be19fdba8faa24d1828e0309b5197fe65ca8602dbe54c9320d7392d0c3d11e7943c56e5dbe0f202c49eac39b3a9f781fdc3147e8df935aebc05c82eaf8cf4f847d91e6836";
$client->B = $object->body->srp->B;
$client->salt = $object->body->srp->B;
$client->salt_resource = gmp_init($object->body->srp->salt, 16);
$client->B_resource = gmp_init($client->B, 16);
$client->k();
$client->x();
$client->u();
print "U: ".$client->u;
exit;
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