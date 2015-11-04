<?PHP
abstract class errorCodes
{
        // TODO - dynamic - load from environment dir
	// next error code is 134
	const ERROR_DEBUG_INFORMATION = 0; // Error messages default to this constant, and will NOT be displayed to users without debug enabled
	// All other errors should be alphabetical
	const ERROR_ACCOUNT_DISABLED = 1;
	const ERROR_ACCOUNT_NOT_FOUND = 2;
	const ERROR_ACCOUNT_NOT_ACTIVE = 3;
	const ERROR_ACCOUNT_SUSPENDED = 4;
	const ERROR_ACTION_NOT_FOUND = 5;
	const ERROR_BAD_CONFIG_DATA = 6;
	const ERROR_BAD_LOGIN_DATA = 7;
	const ERROR_BAD_REQUEST = 8;
	const ERROR_BAD_SYNTAX = 9;
        const ERROR_INTERNAL_ERROR = 10;
        const ERROR_ADMIN_PERMISSION_DENIED = 12;
	const ERROR_CHALLENGE_BAD = 13;
	const ERROR_CONTENT_LENGTH_NOT_SET = 14;
	const ERROR_CONTENT_TYPE_NOT_SET = 15;
	const ERROR_COULD_NOT_CONNECT_TO_CACHE = 91;
	const ERROR_COULD_NOT_CONNECT_TO_DATABASE = 16;
	const ERROR_DATABASE_NOT_FOUND = 93;
	const ERROR_EMAIL_IN_USE = 18;
	const ERROR_EMAIL_INVALID = 84;
	const ERROR_EXPECTATION_FAILED = 19;
	const ERROR_INVALID_ID = 86;
	const ERROR_INVALID_IP = 33;
	const ERROR_INVALID_REGION = 87;
	const ERROR_INVALID_REQUEST = 34;
	const ERROR_INVALID_REQUEST_METHOD = 35;
	const ERROR_INVALID_SLAVE = 85;
	const ERROR_LOGIN_IN_PROCESS = 44;
	const ERROR_MAINTENANCE = 45;
	const ERROR_METHOD_NOT_FOUND = 47;
	const ERROR_NAME_INVALID_CHARACTERS = 51;
	const ERROR_NAME_INVALID_GRAMMAR = 52;
	const ERROR_NAME_INVALID_LENGTH = 53;
	const ERROR_NO_ACTION = 54;
	const ERROR_NO_PASSWORD = 55;
	const ERROR_NO_REQUEST = 56;
	const ERROR_PASSWORD_BAD = 58;
	const ERROR_PASSWORD_INCORRECT = 59;
	const ERROR_PASSWORD_REQUIRED = 60;
	const ERROR_PASSWORD_TRY_LIMIT = 97;
	const ERROR_PAYMENT_FAILURE = 106;
	const ERROR_PAYMENT_DECLINED = 108;
	const ERROR_PAYMENT_ALREADY_COMPLETE = 110;
	const ERROR_PURCHASE_FAILED = 65;
	const ERROR_QUEST_CUSTOM_REWARD_NOT_FOUND = 109;
	const ERROR_REMOTE_API_FAILURE = 94;
	const ERROR_REQUEST_TIMED_OUT = 66;
	const ERROR_REQUEST_NOT_SECURE = 67;
	const ERROR_RESOURCE_NOT_FOUND = 68;
	const ERROR_REWARD_UNAVAILABLE = 69;
	const ERROR_RMT_DISABLED = 121;
	const ERROR_SESSION_INVALID = 70;
	const ERROR_SESSION_NOT_FOUND = 88;
	const ERROR_TOO_MANY_REQUESTS = 75;
	const ERROR_TWO_FACTOR_AUTH_REQUIRED = 98;
	const ERROR_UNAUTHORIZED = 76;
	const ERROR_UNAUTHORIZED_ACTION = 77;
	const ERROR_UNKNOWN_PAYMENT_METHOD = 78;

	//Error levels for logging errors
	const LOG_LEVEL_NONE = 0;
	const LOG_LEVEL_CRITICAL = 1;
	const LOG_LEVEL_ERROR = 2;
	const LOG_LEVEL_WARNING = 3;
	const LOG_LEVEL_INFO = 4;
	const LOG_LEVEL_DEBUG = 5;
	const LOG_LEVEL_ALL = 99;


	public static $codes = array(
		self::ERROR_DEBUG_INFORMATION => array(
			'name' => 'error_debug_info',
			'status' => 500,
			'message' => 'Debug Info.',
			'log_level' => self::LOG_LEVEL_DEBUG,
			'client_error' => false
		),
		self::ERROR_UNAUTHORIZED => array(
			'name' => 'error_unauthorized',
			'status' => 401,
			'message' => 'Unauthorized Action.',
			'log_level' => self::LOG_LEVEL_INFO,
			'client_error' => true
		),
		self::ERROR_ACCOUNT_DISABLED => array(
			'name' => 'error_account_disabled',
			'status' => 404,
			'message' => 'This account is disabled.',
			'log_level' => self::LOG_LEVEL_INFO,
			'client_error' => true
		),
		self::ERROR_ACCOUNT_NOT_FOUND => array(
			'name' => 'error_account_not_found',
			'status' => 404,
			'message' => 'Account Not Found.',
			'log_level' => self::LOG_LEVEL_INFO,
			'client_error' => true
		),
		self::ERROR_ACTION_NOT_FOUND => array(
			'name' => 'error_action_not_found',
			'status' => 404,
			'message' => 'Action Not Found.',
			'log_level' => self::LOG_LEVEL_WARNING,
			'client_error' => false
		),
		self::ERROR_ADMIN_PERMISSION_DENIED => array(
			'name' => 'error_admin_permission_denied',
			'status' => 401,
			'message' => 'You do not have permission to perform that action.',
			'log_level' => self::LOG_LEVEL_INFO,
			'client_error' => false
		),
		self::ERROR_BAD_CONFIG_DATA => array(
			'name' => 'error_bad_config_data',
			'status' => 500,
			'message' => 'Invalid Model Config Item.',
			'log_level' => self::LOG_LEVEL_ERROR,
			'client_error' => false
		),
		self::ERROR_BAD_LOGIN_DATA => array(
			'name' => 'error_bad_login_data',
			'status' => 400,
			'message' => 'Bad login data.',
			'log_level' => self::LOG_LEVEL_ERROR,
			'client_error' => true
		),
		self::ERROR_BAD_REQUEST => array(
			'name' => 'error_bad_request',
			'status' => 400,
			'message' => 'Bad Request.',
			'log_level' => self::LOG_LEVEL_WARNING,
			'client_error' => false
		),
		self::ERROR_BAD_SYNTAX => array(
			'name' => 'error_bad_syntax',
			'status' => 400,
			'message' => 'Bad syntax.',
			'log_level' => self::LOG_LEVEL_WARNING,
			'client_error' => false
		),
		self::ERROR_CHALLENGE_BAD => array(
			'name' => 'error_challenge_bad',
			'status' => 401,
			'message' => 'Bad Challange.',
			'log_level' => self::LOG_LEVEL_INFO,
			'client_error' => false
		),
		self::ERROR_CONTENT_LENGTH_NOT_SET => array(
			'name' => 'error_content_length_not_set',
			'status' => 411,
			'message' => 'Content Length Not Set.',
			'log_level' => self::LOG_LEVEL_WARNING,
			'client_error' => false
		),
		self::ERROR_CONTENT_TYPE_NOT_SET => array(
			'name' => 'error_content_type_not_set',
			'status' => 412,
			'message' => 'Content Type Not Set.',
			'log_level' => self::LOG_LEVEL_WARNING,
			'client_error' => false
		),
		self::ERROR_COULD_NOT_CONNECT_TO_CACHE => array(
			'name' => 'error_could_not_connect_to_cache',
			'status' => 500,
			'message' => 'Server Error: could not connect to cache.',
			'log_level' => self::LOG_LEVEL_CRITICAL,
			'client_error' => true
		),
		self::ERROR_COULD_NOT_CONNECT_TO_DATABASE => array(
			'name' => 'error_could_not_connect_to_database',
			'status' => 500,
			'message' => 'Server Error: could not connect to database.',
			'log_level' => self::LOG_LEVEL_CRITICAL,
			'client_error' => true
		),
		self::ERROR_DATABASE_NOT_FOUND => array(
			'name' => 'error_database_not_found',
			'status' => 500,
			'message' => 'Server Error: Could not find database.',
			'log_level' => self::LOG_LEVEL_CRITICAL,
			'client_error' => true
		),
		self::ERROR_EMAIL_IN_USE => array(
			'name' => 'error_email_in_use',
			'status' => 400,
			'message' => 'This email is already in use.',
			'log_level' => self::LOG_LEVEL_INFO,
			'client_error' => true
		),
		self::ERROR_EMAIL_INVALID => array(
			'name' => 'error_email_invalid',
			'status' => 400,
			'message' => 'This email is invalid.',
			'log_level' => self::LOG_LEVEL_INFO,
			'client_error' => true
		),
		self::ERROR_EXPECTATION_FAILED => array(
			'name' => 'error_expectation_failed',
			'status' => 417,
			'message' => 'Missing or invalid input on web request.',
			'log_level' => self::LOG_LEVEL_WARNING,
			'client_error' => true
		),
		self::ERROR_INTERNAL_ERROR => array(
			'name' => 'error_server_error',
			'status' => 500,
			'message' => 'Server Error.',
			'log_level' => self::LOG_LEVEL_ERROR,
			'client_error' => true
		),
		self::ERROR_INVALID_ID => array(
			'name' => 'error_invalid_id',
			'status' => 401,
			'message' => 'Invalid ID.',
			'log_level' => self::LOG_LEVEL_INFO,
			'client_error' => false
		),
		self::ERROR_INVALID_IP => array(
			'name' => 'error_invalid_ip',
			'status' => 401,
			'message' => 'Invalid IP.',
			'log_level' => self::LOG_LEVEL_INFO,
			'client_error' => false
		),
		self::ERROR_INVALID_REGION => array(
			'name' => 'error_invalid_region',
			'status' => 400,
			'message' => 'Invalid region.',
			'log_level' => self::LOG_LEVEL_WARNING,
			'client_error' => false
		),
		self::ERROR_INVALID_REQUEST => array(
			'name' => 'error_invalid_request',
			'status' => 400,
			'message' => 'Invalid request.',
			'log_level' => self::LOG_LEVEL_WARNING,
			'client_error' => false
		),
		self::ERROR_INVALID_REQUEST_METHOD => array(
			'name' => 'error_invalid_request_method',
			'status' => 400,
			'message' => 'Invalid request method.',
			'log_level' => self::LOG_LEVEL_WARNING,
			'client_error' => false
		),
		self::ERROR_INVALID_SLAVE => array(
			'name' => 'error_invalid_slave',
			'status' => 400,
			'message' => 'Invalid slave.',
			'log_level' => self::LOG_LEVEL_WARNING,
			'client_error' => false
		),
		self::ERROR_LOGIN_IN_PROCESS => array(
			'name' => 'error_login_in_process',
			'status' => 400,
			'message' => 'There is a login in process for this account; try again in 30 seconds.',
			'log_level' => self::LOG_LEVEL_INFO,
			'client_error' => true
		),
		self::ERROR_MAINTENANCE => array(
			'name' => 'error_maintenance',
			'status' => 503,
			'message' => 'The system is currently under maintenance; please try again later.',
			'log_level' => self::LOG_LEVEL_DEBUG,
			'client_error' => true
		),
		self::ERROR_METHOD_NOT_FOUND => array(
			'name' => 'error_method_not_found',
			'status' => 404,
			'message' => 'Method not found.',
			'log_level' => self::LOG_LEVEL_WARNING,
			'client_error' => false
		),
		self::ERROR_NAME_INVALID_CHARACTERS => array(
			'name' => 'error_name_invalid_characters',
			'status' => 400,
			'message' => 'Name may only contain a-Z 0-9 underscores "`" and start with a letter.',
			'log_level' => self::LOG_LEVEL_INFO,
			'client_error' => true
		),
		self::ERROR_NAME_INVALID_GRAMMAR => array(
			'name' => 'error_name_invalid_grammar',
			'status' => 400,
			'message' => 'Name should start with a letter, not end with a space, and not have two spaces in a row.',
			'log_level' => self::LOG_LEVEL_INFO,
			'client_error' => true
		),
		self::ERROR_NAME_INVALID_LENGTH => array(
			'name' => 'error_name_invalid_length',
			'status' => 400,
			'message' => 'Name should be at least 3 characters, and less than 16.',
			'log_level' => self::LOG_LEVEL_INFO,
			'client_error' => true
		),
		self::ERROR_NO_ACTION => array(
			'name' => 'error_no_action',
			'status' => 400,
			'message' => 'Unknown Action.',
			'log_level' => self::LOG_LEVEL_WARNING,
			'client_error' => false
		),
		self::ERROR_NO_PASSWORD => array(
			'name' => 'error_no_password',
			'status' => 404,
			'message' => 'Password login is disabled for this account.',
			'log_level' => self::LOG_LEVEL_WARNING,
			'client_error' => true
		),
		self::ERROR_NO_REQUEST => array(
			'name' => 'error_no_request',
			'status' => 400,
			'message' => 'No request.',
			'log_level' => self::LOG_LEVEL_INFO,
			'client_error' => false
		),
		self::ERROR_PASSWORD_BAD => array(
			'name' => 'error_password_bad',
			'status' => 400,
			'message' => 'Invalid Password; passwords must be at least 4 characters.',
			'log_level' => self::LOG_LEVEL_INFO,
			'client_error' => true
		),
		self::ERROR_PASSWORD_INCORRECT => array(
			'name' => 'error_password_incorrect',
			'status' => 401,
			'message' => 'The entered password is incorrect.',
			'log_level' => self::LOG_LEVEL_INFO,
			'client_error' => true
		),
		self::ERROR_PASSWORD_REQUIRED => array(
			'name' => 'error_password_required',
			'status' => 401,
			'message' => 'You must have a password set before you may do this action.',
			'log_level' => self::LOG_LEVEL_INFO,
			'client_error' => true
		),
		self::ERROR_PASSWORD_TRY_LIMIT => array(
			'name' => 'error_password_try_limit',
			'status' => 401,
			'message' => 'You have made too many incorrect login attempts; please try again later.',
			'log_level' => self::LOG_LEVEL_INFO,
			'client_error' => true
		),
		self::ERROR_PAYMENT_FAILURE => array(
			'name' => 'error_payment_failure',
			'status' => 400,
			'message' => 'There was an error with your payment; please try again later.',
			'log_level' => self::LOG_LEVEL_INFO,
			'client_error' => true
		),
		self::ERROR_PAYMENT_DECLINED => array(
			'name' => 'error_payment_declined',
			'status' => 400,
			'message' => 'Your payment has been declined; please try again with a different payment method.',
			'log_level' => self::LOG_LEVEL_INFO,
			'client_error' => true
		),
		self::ERROR_PAYMENT_ALREADY_COMPLETE => array(
			'name' => 'error_payment_already_complete',
			'status' => 400,
			'message' => 'Your payment has already been completed.',
			'log_level' => self::LOG_LEVEL_INFO,
			'client_error' => true
		),
		self::ERROR_PURCHASE_FAILED => array(
			'name' => 'error_purchase_failed',
			'status' => 400,
			'message' => 'Your purchase could not be completed; please try again later.',
			'log_level' => self::LOG_LEVEL_INFO,
			'client_error' => true
		),
		self::ERROR_REMOTE_API_FAILURE => array(
			'name' => 'error_remote_api_failure',
			'status' => 400,
			'message' => 'There was an error on a remote server; please try again later.',
			'log_level' => self::LOG_LEVEL_ERROR,
			'client_error' => true
		),
		self::ERROR_REQUEST_TIMED_OUT => array(
			'name' => 'error_request_timed_out',
			'status' => 408,
			'message' => 'Your request took too long to reach the server; try again.',
			'log_level' => self::LOG_LEVEL_INFO,
			'client_error' => true
		),
		self::ERROR_REQUEST_NOT_SECURE => array(
			'name' => 'error_request_not_secure',
			'status' => 401,
			'message' => 'This request must be made securely: please use HTTPS.',
			'log_level' => self::LOG_LEVEL_INFO,
			'client_error' => false
		),
		self::ERROR_RESOURCE_NOT_FOUND => array(
			'name' => 'error_resource_not_found',
			'status' => 404,
			'message' => 'The requested resource was not found.',
			'log_level' => self::LOG_LEVEL_INFO,
			'client_error' => true
		),
		self::ERROR_RMT_DISABLED => array(
			'name' => 'error_rmt_disabled',
			'status' => 401,
			'message' => 'Purchases have been disabled on this account.  Contact support for more information.',
			'log_level' => self::LOG_LEVEL_WARNING,
			'client_error' => true
		),
		self::ERROR_SESSION_INVALID => array(
			'name' => 'error_session_invalid',
			'status' => 401,
			'message' => 'Your session is invalid.  Please login again.',
			'log_level' => self::LOG_LEVEL_INFO,
			'client_error' => true
		),
		self::ERROR_SESSION_NOT_FOUND => array(
			'name' => 'error_session_not_found',
			'status' => 401,
			'message' => 'There is no active session.',
			'log_level' => self::LOG_LEVEL_INFO,
			'client_error' => false
		),
		self::ERROR_TOO_MANY_REQUESTS => array(
			'name' => 'error_too_many_requests',
			'status' => 429,
			'message' => 'You have made too many requests recently.',
			'log_level' => self::LOG_LEVEL_WARNING,
			'client_error' => true
		),
		self::ERROR_TWO_FACTOR_AUTH_REQUIRED => array(
			'name' => 'error_two_factor_auth_required',
			'status' => 401,
			'message' => 'You must enter your two-factor auth token.',
			'log_level' => self::LOG_LEVEL_INFO,
			'client_error' => false
		),
		self::ERROR_UNAUTHORIZED_ACTION => array(
			'name' => 'error_unauthorized_action',
			'status' => 401,
			'message' => 'Unauthorized Action.',
			'log_level' => self::LOG_LEVEL_WARNING,
			'client_error' => false
		),
		self::ERROR_UNKNOWN_PAYMENT_METHOD => array(
			'name' => 'error_unknown_payment_method',
			'status' => 402,
			'message' => 'Unknown payment method.',
			'log_level' => self::LOG_LEVEL_INFO,
			'client_error' => true
		),
	);

	public static function stringtableFromCode($code)
	{
		return self::$codes[$code]['name'];
	}

	public static function messageFromCode($code)
	{
		return self::$codes[$code]['message'];
	}

	public static function headerFromCode($code)
	{
		return self::$codes[$code]['status'];
	}

	public static function logLevelFromCode($code)
	{
		return self::$codes[$code]['log_level'];
	}

	public static function codeFromName($name)
	{
		foreach (self::$codes as $code => $data)
		{
			if ($data['name'] == $name)
			{
				return $code;
			}
		}
		return false;
	}

	public static function codeFromMessage($message)
	{
		foreach (self::$codes as $code => $data)
		{
			if ($data['message'] == $message)
			{
				return $code;
			}
		}
		return false;
	}

	public static function logLevelNameFromLogLevel($logLevel)
	{
		switch ($logLevel)
		{
			case self::LOG_LEVEL_ALL:
				return 'All';
			case self::LOG_LEVEL_CRITICAL:
				return 'Critical';
			case self::LOG_LEVEL_ERROR:
				return 'Error';
			case self::LOG_LEVEL_WARNING:
				return 'Warning';
			case self::LOG_LEVEL_INFO:
				return 'Info';
			case self::LOG_LEVEL_DEBUG:
				return 'Debug';
			case self::LOG_LEVEL_ALL:
				return 'All';
			default:
				return 'Unknown Log Level';
		}
	}

	public static function headerFromMessage($message)
	{
		return self::headerFromCode(self::codeFromMessage($message));
	}
}