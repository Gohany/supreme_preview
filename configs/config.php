<?PHP
//Unicode settings
{
	//Set multibyte strings to UTF-8
	mb_internal_encoding('UTF-8');

	//Regex is UTF-8 aware
	mb_regex_encoding('UTF-8');
}

//Set PHP timezone to UTC
date_default_timezone_set('UTC');

//Maintenance mode will reject all requests with a SUPREME maintenance error.  Use this in cases of database maintenance or during patching.
define('__MAINTENANCE__', false);

define('__MYSQL_PERSISTENT__', false);

//Debug Options
{
	//If less than this error level has occured, log it to disk.  LOG_LEVEL_NONE to disable.
	define('__DEBUG_LOG_LEVEL__', errorCodes::LOG_LEVEL_WARNING);

	//Output any diagnostic information at all.  Switching this false will override the following options.
//	if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] == '10.0.2.2')
//	{
		define('__DEBUG__', true);
//	}
//	else
//	{
//		define('__DEBUG__', false);
//	}

	//Display queries in debug mode?
	define('__DEBUG_DISPLAY_QUERY_LOG__', true);

	//Display queries in debug mode?
	define('__DEBUG_DISPLAY_QUERY_EXECUTION_TIMES__', false);

	//Display output log in debug mode?
	define('__DEBUG_DISPLAY_OUTPUT__', true);

	//Display input log in debug mode?
	define('__DEBUG_DISPLAY_INPUT__', true);

	//Display full exceptions in debug mode?
	define('__DEBUG_DISPLAY_EXCEPTIONS__', true);

	//Display memory usage in debug mode?
	define('__DEBUG_DISPLAY_MEMORY__', true);
        
        // show friendly error output
        define('__DEBUG_VERBOSE_ERRORS__', true);
}

//Real Money Configuration!
{
	//Sandbox / test config
	define("AUTHORIZENET_SANDBOX", true);
	define("AUTHORIZENET_API_LOGIN_ID", 'APILOGIN');
	define("AUTHORIZENET_TRANSACTION_KEY", 'TRANSACTIONKEY');

	define("AUTHORIZENET_LOG_FILE", false);

	//Sandbox / test config
	define("PAYPAL_SANDBOX", true);
	define("PAYPAL_CLIENT_ID", 'CLIENTID');
	define("PAYPAL_CLIENT_SECRET", 'CLIENTSECRET');
}

//Send emails?  For development, you probably want this off.
define('__SEND_EMAILS__', false);

//Run gearman jobs?
define('__RUN_GEARMAN_JOBS__', true);

//PHP settings
{
	error_reporting(E_ALL);
	ini_set('display_errors', true);
	ini_set('log_errors', true);
	ini_set('html_errors', false);
	ini_set('log_errors_max_len', 1000000);
	ini_set('ignore_repeated_errors', true);
}