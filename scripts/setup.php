<?php
if (PHP_SAPI !== 'cli')
{
	die('This script must be ran from command-line.' . PHP_EOL);
}

if (!isset($_SERVER['_HTDOCS_']))
{
	$_SERVER['_HTDOCS_'] = getcwd();
}

echo '##' . PHP_EOL;
echo '# SUPREME Setup Script' . PHP_EOL;
echo '##' . PHP_EOL;

//DB init
{
	require_once $_SERVER['_HTDOCS_'] . '/scripts/createDatabase.php';
	require_once $_SERVER['_HTDOCS_'] . '/scripts/updateEntities.php';
	require_once $_SERVER['_HTDOCS_'] . '/scripts/createDebugPatchEntry.php';
	require_once $_SERVER['_HTDOCS_'] . '/scripts/flushRedis.php';
	require_once $_SERVER['_HTDOCS_'] . '/scripts/flushMemcached.php';
}

//Admin Account Creation
{
	do
	{
		echo 'Create Admin? [Y/N]: ';
		fscanf(STDIN, "%s\n", $createAdminInput);
		$createAdminInput = strtolower($createAdminInput);
	}
	while ($createAdminInput !== 'y' && $createAdminInput !== 'n');

	if ($createAdminInput === 'y')
	{
		require_once $_SERVER['_HTDOCS_'] . '/scripts/createAdmin.php';
	}
}

//Account Creation
{
	do
	{
		echo 'Create User Account? [Y/N]: ';
		fscanf(STDIN, "%s\n", $createUserAccountInput);
		$createUserAccountInput = strtolower($createUserAccountInput);
	}
	while ($createUserAccountInput !== 'y' && $createUserAccountInput !== 'n');

	if ($createUserAccountInput === 'y')
	{
		require_once $_SERVER['_HTDOCS_'] . '/scripts/createUser.php';
	}
}