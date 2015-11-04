<?php
require_once $_SERVER['_HTDOCS_'] . '/base_include.php';

define('TAB_SIZE', 4);
define('TAB_BUFFER', 5);

$output = array();

foreach (errorCodes::$codes as $errorCode => $error)
{
	//Ignore more than first occurance of string table name, or non-client errors
	if (isset($output[$error['name']]) || $errorCode <= 0 || (isset($error['client_error']) && !$error['client_error']))
	{
		continue;
	}

	//Save for output
	$output[$error['name']] = $error['message'];

	//Game String Table Format?
	if (empty($_REQUEST['kata']))
	{
		//Determine if we need to increase our tab size
		$tabAmount = floor(strlen($error['name']) / TAB_SIZE);
		if (!isset($tabHighestAmount) || $tabAmount > $tabHighestAmount)
		{
			$tabHighestAmount = $tabAmount;
		}
	}
}

if (empty($_REQUEST['kata']))
{
	//Game Error String Table Ouput
	echo '<pre>';
	foreach ($output as $name => $message)
	{
		echo $name;

		//Determine how many tabs we need to output to have everything line up because we're sperglords
		for ($i = floor(strlen($name) / TAB_SIZE) - TAB_BUFFER; $i < $tabHighestAmount; $i++)
		{
			echo "\t";
		}

		echo $message . "\n";
	}
	echo '</pre>';
}
else
{
	//PHP Array Error String Table Output
	ksort($output);
	echo '<pre>' . var_export($output, true) . '</pre>';
}