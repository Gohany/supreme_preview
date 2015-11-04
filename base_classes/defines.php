<?php
//Random defines 4 u code
define('SECONDS_IN_MINUTE', 60);
define('SECONDS_IN_HOUR', SECONDS_IN_MINUTE * 60);
define('SECONDS_IN_DAY', SECONDS_IN_HOUR * 24);
define('SECONDS_IN_WEEK', SECONDS_IN_DAY * 7);
define('SECONDS_IN_MONTH', SECONDS_IN_DAY * 30);

//Define SUPREME Version
{
	$versionFile = $_SERVER['_HTDOCS_'] . '/configs/version.json';
	if (is_file($versionFile))
	{
		$jsonString = @file_get_contents($versionFile);
		if ($jsonString)
		{
			$versionObject = @json_decode($jsonString);
			if ($versionObject && property_exists($versionObject, 'version'))
			{
				define('SUPREME_VERSION', $versionObject->version);
			}
		}
	}

	if (!defined('SUPREME_VERSION'))
	{
		define('SUPREME_VERSION', '0.0.0.0');
	}
}

//Determine SUPREME branch
{
	foreach (array('prod', 'dev') as $location)
	{
		if (isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], $location) !== false)
		{
			define('SUPREME_BRANCH', $location);
			break;
		}
	}

	if (!defined('SUPREME_BRANCH'))
	{
		define('SUPREME_BRANCH', 'dev');
	}
}