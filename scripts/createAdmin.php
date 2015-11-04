<?php
if (PHP_SAPI !== 'cli')
{
	die('This script must be ran from command-line.' . PHP_EOL);
}

if (!isset($_SERVER['_HTDOCS_']))
{
	$_SERVER['_HTDOCS_'] = getcwd();
}

require_once $_SERVER['_HTDOCS_'] . '/base_include.php';
require_once $_SERVER['_HTDOCS_'] . '/environments/base/models/admin.php';


echo PHP_EOL . '##' . PHP_EOL;
echo '# SUPREME Admin Creation Tool' . PHP_EOL;
echo '##' . PHP_EOL . PHP_EOL;

do
{
	if (isset($email))
	{
		echo 'Invalid Email.  Try again.' . PHP_EOL;
	}

	echo 'Admin Email (Username): ';

	fscanf(STDIN, "%s\n", $email);
}
while (filter_var($email, FILTER_VALIDATE_EMAIL) === false);

do
{
	if (isset($password))
	{
		echo 'Invalid password.  Try again.' . PHP_EOL;
	}

	echo 'Admin Password: ';

	fscanf(STDIN, "%s\n", $password);
}
while (valid::password($password) !== true);

do
{
	if (isset($displayName))
	{
		echo 'Invalid display name.  Try again.' . PHP_EOL;
	}

	echo 'Admin Display Name: ';

	fscanf(STDIN, "%s\n", $displayName);
}
while (valid::nickname($displayName) !== true);

do
{
	if (isset($firstName))
	{
		echo 'Invalid first name.  Try again.' . PHP_EOL;
	}
	echo 'Admin First Name: ';

	fscanf(STDIN, "%s\n", $firstName);
}
while (valid::realName($firstName) !== true);

do
{
	if (isset($lastName))
	{
		echo 'Invalid last name.  Try again.' . PHP_EOL;
	}
	echo 'Admin Last Name: ';

	fscanf(STDIN, "%s\n", $lastName);
	echo PHP_EOL;
}
while (valid::realName($lastName) !== true);

try
{
	$adminAccount = baseAdminModel::create($email, $password, $displayName, $firstName, $lastName);
	echo 'Successfully created admin id ' . $adminAccount->getAdminId() . PHP_EOL;
}
catch (error $e)
{
	echo 'There was an error creating this account.' . PHP_EOL;
	error::listErrors();
}