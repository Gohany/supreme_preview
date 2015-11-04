<?php
class log
{

	public static function logRequest($requestType = 'Error')
	{
		if (ob_get_length() > 0)
		{
			$outputLog = ob_get_contents();
		}
		else
		{
			$outputLog = 'No Output';
		}

		$requestInfo = '';
		{
			$requestInfo .= 'Session Header: ';
			if (isset($_SERVER[dispatcher::AUTHENTICATION_HEADER]))
			{
				$requestInfo .= $_SERVER[dispatcher::AUTHENTICATION_HEADER] . PHP_EOL;
			}
			else
			{
				$requestInfo .= 'Not sent' . PHP_EOL;
			}

			$requestInfo .= 'Request meta-data: ' . print_r(dispatcher::getRequestMetaData(), true);
		}

		$errorList = error::getErrorList(true);
		if (!$errorList)
		{
			$errorList = array('N/A');
		}
		$stackTrace = implode('', $errorList);

		if (!$queryLog = dataStore::getArray('__QUERY_ARRAY__', 'queryLog'))
		{
			$queryLog = array();
		}
		$queryList = print_r($queryLog, true);

		if (empty($_REQUEST))
		{
			$_REQUEST = array();
		}
		$inputList = print_r($_REQUEST, true);

		$matches = null; //init variable for IDE
		if (preg_match('/\[card_num\] \=\>\s+([\d\ \-]+)+/i', $inputList, $matches) > 0)
		{
			$inputList = str_replace($matches[0], str_replace($matches[1], str_pad('', strlen($matches[1]), 'X'), $matches[0]), $inputList);
		}
		if (preg_match('/\[exp_date\] \=\>\s+([\d\ \-\/]+)+/i', $inputList, $matches) > 0)
		{
			$inputList = str_replace($matches[0], str_replace($matches[1], str_pad('', strlen($matches[1]), 'X'), $matches[0]), $inputList);
		}
		if (preg_match('/\[card_code\] \=\>\s+([\d\ \-]+)+/i', $inputList, $matches) > 0)
		{
			$inputList = str_replace($matches[0], str_replace($matches[1], str_pad('', strlen($matches[1]), 'X'), $matches[0]), $inputList);
		}
		if (preg_match('/\[newPassword\] \=\>\s+(.+)+/i', $inputList, $matches) > 0)
		{
			$inputList = str_replace($matches[0], str_replace($matches[1], str_pad('', strlen($matches[1]), 'X'), $matches[0]), $inputList);
		}

		$logFormat = '

##
# SUPREME ' . ucwords($requestType) . ' Log
##

## Request Info ##

%s
## Input Vars ##

%s
## Script Output Log ##

%s
## Stack Trace ##

%s
## Query List ##

%s
##
# End SUPREME ' . ucwords($requestType) . ' Log
##

';
		error_log(sprintf($logFormat, $requestInfo, $stackTrace, $queryList, $inputList, $outputLog));
	}
}