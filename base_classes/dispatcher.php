<?PHP
class dispatcher
{
	##
	# Constants
	##
        const REQUESTER_SERVER = 's';
	const REQUESTER_CLIENT = 'c';
	const REQUESTER_ADMIN = 'a';
        const REQUESTER_ACCOUNT = 'ac';
	const REQUESTER_UNKNOWN_CLIENT = '';
	//Environments
        // TODO
        // make dynamic
	const ENVIRONMENT_BASE = 'base';
        const ENVIRONMENT_BROADCAST = 'broadcast';
        const ENVIRONMENT_FRONTEND = 'frontend';
        const ENVIRONMENT_HRS = 'hrs';
	const BRUTE_FORCE_STRICT = 1;
	const BRUTE_FORCE_NORMAL = 2;
	const BRUTE_FORCE_LOOSE = 3;
	const BRUTE_FORCE_DEFAULT = 3;
	const BRUTE_FORCE_PREFIX = 'SUPREME_BRUTEFORCE_';
        const AUTH_TOKEN_DELIMITER = '/';
	##
	# Properties
	##
	public $input = array();
	public $output;
	public $action;
	public $controller;
	public $language = 'en';
	public $call;
	public $resourceArray;
	public $headers;
	public $requester;
        public $requester_raw;
	public $X_signature;
	public $X_requester = clientInfo::CLIENTTYPE_CLIENT;
	public $X_environment;
	public $X_id;
	public $X_slot;
	public $httpSuccessfulStatusCode;
	public $environment;
	public $bruteForceLevel;
	public $bruteForceScore;
	public $bruteForceRedisKey;
        
	public static $bruteForceProtection = array(
		'session' => array(
			'create' => self::BRUTE_FORCE_STRICT,
		),
		'authenticate' => array(
			'create' => self::BRUTE_FORCE_STRICT,
		)
	);
	public static $bruteForceRules = array(
		self::BRUTE_FORCE_STRICT => array(
			'lockoutMin' => 15,
			'maxLockouts' => 1,
			'expire' => 900,
		),
		self::BRUTE_FORCE_NORMAL => array(
			'lockoutMin' => 25,
			'maxLockouts' => 0,
			'expire' => 900,
		),
		self::BRUTE_FORCE_LOOSE => array(
			'lockoutMin' => 0,
			'maxLockouts' => 0,
			'expire' => 900,
		)
	);

	##
	# All controllers must be on this list, or requests will be rejected
	##
	public static $controllers = array(
		'user',
                'authenticate',
                'session',
                'emailList',
                'domain',
                'form',
                'admin',
                'adminAuthenticate',
                'adminSession',
                'account',
                'server',
                'serverSession',
                'serverAuthenticate',
                'serverStatus',
	);
	protected static $languages = array(
		'br',
		'de',
		'en',
		'es',
		'fr',
		'ko',
		'kr',
		'pi',
		'pt',
		'ro',
		'ru',
		'ta',
		'th',
		'vn',
		'zh'
	);
	public static $requesters = array(
		self::REQUESTER_CLIENT => 'client',
		self::REQUESTER_ADMIN => 'admin',
		self::REQUESTER_UNKNOWN_CLIENT => 'none',
                self::REQUESTER_ACCOUNT => 'account',
                self::REQUESTER_SERVER => 'server',
	);
	public static $environments = array(
		self::ENVIRONMENT_BASE,
                self::ENVIRONMENT_BROADCAST,
                self::ENVIRONMENT_FRONTEND,
                self::ENVIRONMENT_HRS,
	);
	public static $filters = array(
		FILTER_VALIDATE_BOOLEAN => 'bool',
		FILTER_VALIDATE_EMAIL => 'email',
		FILTER_VALIDATE_FLOAT => 'float',
		FILTER_VALIDATE_INT => 'int',
		FILTER_VALIDATE_IP => 'ip',
		FILTER_VALIDATE_REGEXP => 'regex',
		FILTER_VALIDATE_URL => 'url'
	);
        

	const AUTHENTICATION_HEADER = 'HTTP_X_AUTHORIZATION';

	function __construct()
	{
		//Make sure there is a resource specified
		if (!isset($_SERVER['REDIRECT_URL']))
		{
			throw new error(errorCodes::ERROR_NO_REQUEST);
		}

		switch (strtoupper($_SERVER['REQUEST_METHOD']))
		{
			case 'GET':
				$this->action = 'display';
				$this->httpSuccessfulStatusCode = 200;
				break;
			case 'POST':
				$this->action = 'create';
				$this->httpSuccessfulStatusCode = 201;
				break;
			case 'PUT':
				$this->action = 'modify';
				$this->httpSuccessfulStatusCode = 200;
				break;
			case 'DELETE':
				$this->action = 'delete';
				$this->httpSuccessfulStatusCode = 200;
				break;
			default:
				throw new error(errorCodes::ERROR_INVALID_REQUEST_METHOD);
		}

		$this->input = $_REQUEST;

		#if (!in_array(strtoupper($_SERVER['REQUEST_METHOD']), array('GET', 'DELETE')) && !isset($_SERVER['CONTENT_LENGTH']))
		#{
			#throw new error(errorCodes::ERROR_CONTENT_LENGTH_NOT_SET);
		#}

		//Makes nginx and HHVM work
		if (!empty($_SERVER['REDIRECT_URL']) && !empty($_SERVER['SERVER_SOFTWARE']) && (substr($_SERVER['SERVER_SOFTWARE'], 0, 5) === 'nginx' || $_SERVER['SERVER_SOFTWARE'] === 'HPHP'))
		{
			$_SERVER['REDIRECT_URL'] = urldecode($_SERVER['REDIRECT_URL']);
		}

		//Grab the resource location
		$this->resourceArray = explode('/', trim($_SERVER['REDIRECT_URL'], '/'));
		if (count($this->resourceArray) < 3
			|| !in_array($this->resourceArray[0], array_keys(self::$requesters))
			|| !in_array($this->resourceArray[1], self::$environments)
		)
		{
			throw new error(errorCodes::ERROR_NO_REQUEST);
		}

		$this->requester = self::$requesters[$this->resourceArray[0]];
		$this->requester_raw = $this->resourceArray[0];
                
		if (isset($_SERVER[self::AUTHENTICATION_HEADER]))
		{
			$authArray = explode(' ', $_SERVER[self::AUTHENTICATION_HEADER]);
			if (count($authArray) < 3)
			{
				throw new error(errorCodes::ERROR_UNAUTHORIZED);
			}

			$clientType = $authArray[0];
			$environment = $authArray[1];
			$id = $authArray[2];
                        
			$signature = isset($authArray[3]) ? $authArray[3] : null;

			$clientInfo = new clientInfo($clientType, $id, $environment, $signature);

			$this->X_environment = $environment;
			$this->X_id = $id;
			$this->X_requester = $clientType;
			$this->X_signature = $signature;
		}
		else
		{
			$clientInfo = new clientInfo();
		}

		dataStore::setObject('clientInfo', $clientInfo);

		define('ENVIRONMENT', $this->resourceArray[1]);
		$this->environment = $this->resourceArray[1];

		if (!in_array($this->resourceArray[2], self::$controllers))
		{
			throw new error(errorCodes::ERROR_BAD_REQUEST);
		}

		$this->controller = $this->resourceArray[2];

		unset($this->resourceArray[0]);
		unset($this->resourceArray[1]);
		unset($this->resourceArray[2]);
	}

	public function bruteForceCheck()
	{
		// key: SUPREME_BRUTEFORCE_ . geoip::getClientIP()
		// member: implode('.',$this->resourceArray)
		// score: attemps
		// check to see if x number of brute force lockouts have occured
		// check to see if our current path/member is at lockout

		if (!defined('__BRUTEFORCE__CHECK__') || __BRUTEFORCE__CHECK__)
		{
			return;
		}
		if (isset(self::$bruteForceRules[$this->bruteForceLevel]['lockoutMin']) && self::$bruteForceRules[$this->bruteForceLevel]['lockoutMin'] > 0)
		{
			isset($this->bruteForceRedisKey) || $this->bruteForceRedisKey = RedisPool::getRedisKey(self::BRUTE_FORCE_PREFIX . geoip::getClientIP());
			$member = $_SERVER['REDIRECT_URL'];

			$this->bruteForceScore = $this->bruteForceRedisKey->zScore($member);
			if ($this->bruteForceScore >= self::$bruteForceRules[$this->bruteForceLevel]['lockoutMin']
				|| (
				self::$bruteForceRules[$this->bruteForceLevel]['maxLockouts'] > 0
				&& $this->bruteForceRedisKey->zCount(self::$bruteForceRules[$this->bruteForceLevel]['lockoutMin'], '+inf') >= self::$bruteForceRules[$this->bruteForceLevel]['maxLockouts']
				)
			)
			{
				// reject
				$this->bruteForceRedisKey->setTimeout(self::$bruteForceRules[$this->bruteForceLevel]['expire']);
				throw new error(errorCodes::ERROR_TOO_MANY_REQUESTS);
			}
		}

		return true;
	}

	public function addBruteForceAttempt()
	{

		if (!defined('__BRUTEFORCE__CHECK__') || __BRUTEFORCE__CHECK__)
		{
			return;
		}
		if (isset(self::$bruteForceRules[$this->bruteForceLevel]['lockoutMin']) && self::$bruteForceRules[$this->bruteForceLevel]['lockoutMin'] > 0)
		{
			isset($this->bruteForceRedisKey) || $this->bruteForceRedisKey = RedisPool::getRedisKey(self::BRUTE_FORCE_PREFIX . geoip::getClientIP());
			$member = $_SERVER['REDIRECT_URL'];
			$this->bruteForceRedisKey->zIncrBy($member);
		}
		return true;
	}

	public function start()
	{
		if (defined('__MAINTENANCE__') && __MAINTENANCE__ === true)
		{
			throw new error(errorCodes::ERROR_MAINTENANCE);
		}

		//Create the controller object and pass the user and action
		if (!is_file($_SERVER['_HTDOCS_'] . '/environments/' . $this->environment . '/controllers/' . $this->controller . '.php'))
		{
			error::addError('Controller Missing; could not find file "' . $_SERVER['_HTDOCS_'] . '/environments/' . $this->environment . '/controllers/' . $this->controller . '.php".');
			throw new error(errorCodes::ERROR_BAD_REQUEST);
		}
		require_once $_SERVER['_HTDOCS_'] . '/environments/' . $this->environment . '/controllers/' . $this->controller . '.php';

		//Put class name into variable to prevent Netbeans from thinking we're creating an instance of "controller".
		$class = $this->controller;

		$this->call = new $class();

		//Load the input and resource location into the input then run the controller action function
		$this->call->environment = $this->environment;
		$this->call->input = $this->input;
		$this->call->action = $this->action;
		$this->call->controller = $this->controller;
		$this->call->resourceLocation = $this->resourceArray;
		$this->call->requester = $this->requester;
		$this->call->X_environment = $this->X_environment;
		$this->call->X_requester = $this->X_requester;
		$this->call->X_id = $this->X_id;
		$this->call->X_signature = $this->X_signature;
		$this->call->X_slot = $this->X_slot;
		$this->call->statusHeader = $this->httpSuccessfulStatusCode;
                
                $this->call->authToken = self::AUTH_TOKEN_DELIMITER . $this->requester_raw . self::AUTH_TOKEN_DELIMITER . $this->environment . self::AUTH_TOKEN_DELIMITER . $this->controller;
                $this->call->authToken .= self::AUTH_TOKEN_DELIMITER . implode('/',$this->resourceArray);
                $this->call->authToken = rtrim($this->call->authToken, '/');
                
                if (!empty($this->input))
                {
                        $this->call->authToken .= '?';
                        $this->call->authToken .= http_build_query($this->input);
                }
                
		dataStore::setString('requester', $this->requester);
		dataStore::setString('action', $this->action);
		dataStore::setString('environment', $this->environment);
		dataStore::setString('controller', $this->controller);
		dataStore::setString('input', $this->input);

		dispatcher::parseProperties($this->call);
		// do brute force check

		if (isset(self::$bruteForceProtection[$this->controller][$this->action]))
		{
			$this->bruteForceLevel = self::$bruteForceProtection[$this->controller][$this->action];
		}
		else
		{
			// do default
			$this->bruteForceLevel = self::BRUTE_FORCE_DEFAULT;
		}

		$this->bruteForceCheck();
                
		if (!$this->call->authenticate($this->action, $this->controller))
		{
			throw new error(errorCodes::ERROR_UNAUTHORIZED);
		}

		//Check whitelist to see if class can parse properties. This allows requests with a less rigid order of properties and values. If so, parse:
		if (!method_exists($this->controller, $this->action))
		{
			throw new error(errorCodes::ERROR_METHOD_NOT_FOUND);
		}
		// Log All Requests Processed By 'admin' account
                
		if ($this->X_requester == self::REQUESTER_ADMIN && !empty($this->call->authenticationModel))
		{
			$this->call->authenticationModel->logThisRequest($this->call);
		}

		$this->call->{$this->action}();
	}

	public static function parseProperties(&$class)
	{
		//Count the resource locations and begin a for loop that continues as long as there are resource locations, will go by twos as the resource name alternates with the value.
		$c = count($class->resourceLocation);
		for ($i = 3; ($i - 2) < $c; $i = $i + 2)
		{
			//If the resource location is within above array, continue...
			if (!array_key_exists($class->resourceLocation[$i], $class->properties))
			{
				error::addError('Unknown resource ' . utility::addQuotes($class->resourceLocation[$i]));
				throw new error(errorCodes::ERROR_BAD_SYNTAX);
			}
			$key = $class->resourceLocation[$i];

			// Must set a value for the property.
			if (!isset($class->resourceLocation[$i + 1]))
			{
				error::addError('No location specified for resource' . utility::addQuotes($key));
				throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
			}
			$value = $class->resourceLocation[$i + 1];

			// If there is a return function associated with this property, then do it.
			if (isset($class->properties[$key]['FILTER_FUNCTION'], $class->properties[$key]['FILTER_FUNCTION']['class'], $class->properties[$key]['FILTER_FUNCTION']['method']))
			{
				if (isset($key['FILTER_FUNCTION']['include']))
				{
					$file = $_SERVER['_HTDOCS_'] . $key['FILTER_FUNCTION']['include'];
					if (!is_file($file))
					{
						error::addError('File missing; could not find "' . $file . '"');
						throw new error(errorCodes::ERROR_BAD_REQUEST);
					}
					require_once $file;
				}

				$className = $class->properties[$key]['FILTER_FUNCTION']['class'];
				if (!class_exists($className))
				{
					error::addError("Missing class '" . $className . "'.");
					throw new error(errorCodes::ERROR_INTERNAL_ERROR);
				}

				$methodName = $class->properties[$key]['FILTER_FUNCTION']['method'];
				if (!method_exists($className, $methodName))
				{
					error::addError("Missing method '" . $className . "::" . $methodName . "()'.");
					throw new error(errorCodes::ERROR_INTERNAL_ERROR);
				}

				if (call_user_func(array($className, $methodName), $value) !== true)
				{
					error::addError(utility::addQuotes($key) . ' failed validation.');
					throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
				}
			}
			elseif (isset($class->properties[$key]['FILTER']) && filter_var($value, $class->properties[$key]['FILTER']) === false)
			{
				// Filter the value through appropriate filter for type.
				error::addError(utility::addQuotes($key) . " expected as " . utility::addQuotes(self::$filters[$class->properties[$key]['FILTER']]));
				throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
			}

			// If there is a return function associated with this property, then do it.
			if (array_key_exists('RETURN_FUNCTION', $class->properties[$key]))
			{
                                
				$className = $class->properties[$key]['RETURN_FUNCTION']['class'];
                                
                                if (isset($class->properties[$key]['RETURN_FUNCTION']['uid']) && $class->properties[$key]['RETURN_FUNCTION']['uid'] == true)
                                {
                                        $value = utility::pairFromUid($value);
                                }
                                
                                if (isset($class->properties[$key]['RETURN_FUNCTION']['stringLookup']) && in_array($class->properties[$key]['RETURN_FUNCTION']['stringLookup'], stringTables::$stringTables))
                                {
                                        $stringTables = new stringTables($class->properties[$key]['RETURN_FUNCTION']['stringLookup'], $class->environment, $className, $value);
                                        $value = $stringTables->data;
                                }
                                
				if (isset($class->properties[$key]['RETURN_FUNCTION']['property']) && empty($class->properties[$key]['RETURN_FUNCTION']['subObjectMethod']))
				{
                                        
					$propertyName = $class->properties[$key]['RETURN_FUNCTION']['property'];

					if (isset($class->properties[$key]['RETURN_FUNCTION']['includes']))
					{
						if (!is_file($_SERVER['_HTDOCS_'] . $class->properties[$key]['RETURN_FUNCTION']['includes']))
						{
							error::addError('Missing file "' . $class->properties[$key]['RETURN_FUNCTION']['includes'] . "'");
							throw new error(errorCodes::ERROR_INTERNAL_ERROR);
						}
						require_once $_SERVER['_HTDOCS_'] . $class->properties[$key]['RETURN_FUNCTION']['includes'];
					}

					if (!method_exists($className, $class->properties[$key]['RETURN_FUNCTION']['method']))
					{
						error::addError('Missing method "' . $className . '"::"' . $class->properties[$key]['RETURN_FUNCTION']['method'] . '()"');
						throw new error(errorCodes::ERROR_INTERNAL_ERROR);
					}
					$class->{$propertyName} = call_user_func(array($className, $class->properties[$key]['RETURN_FUNCTION']['method']), $value);
				}
				elseif ($class->properties[$key]['RETURN_FUNCTION']['object_property'] && empty($class->properties[$key]['RETURN_FUNCTION']['subObjectMethod']))
				{
                                        
					$propertyName = $class->properties[$key]['RETURN_FUNCTION']['object_property'];
					$class->{$propertyName}->{$class->properties[$key]['RETURN_FUNCTION']['method']}($value);
				}
				elseif ($class->properties[$key]['RETURN_FUNCTION']['subObjectMethod'])
				{
                                        
					$objectPropertyName = $class->properties[$key]['RETURN_FUNCTION']['object_property'];
					$methodName = $class->properties[$key]['RETURN_FUNCTION']['method'];
					$subObjectMethodName = $class->properties[$key]['RETURN_FUNCTION']['subObjectMethod'];
                                        if (!empty($class->properties[$key]['RETURN_FUNCTION']['property']))
                                        {
                                                $propertyName = $class->properties[$key]['RETURN_FUNCTION']['property'];
                                                $class->{$objectPropertyName}->$propertyName = $class->{$objectPropertyName}->{$methodName}()->{$subObjectMethodName}($value);
                                        }
                                        else
                                        {
                                                $class->{$objectPropertyName}->{$methodName}()->{$subObjectMethodName}($value);
                                                //class->object_property->methodName()->subObjectMethod(resource)
                                        }
				}
			}

			$class->{$key} = $value;
			$class->resources[$key] = $value;
		}
	}

	public static function getRequestMetaData()
	{
		if (!isset($_SERVER['SERVER_NAME']))
		{
			$_SERVER['SERVER_NAME'] = '';
		}

		if (!isset($_SERVER['REQUEST_URI']))
		{
			$_SERVER['REQUEST_URI'] = '';
		}

		if (!isset($_SERVER['HTTP_USER_AGENT']))
		{
			$_SERVER['HTTP_USER_AGENT'] = 'Unknown User Agent';
		}

		return array(
			'RequestDate' => date('Y-m-d H:i:s'),
			'RequestUri' => htmlspecialchars($_SERVER['REQUEST_URI']),
			'ServerHostname' => $_SERVER['SERVER_ADDR'],
			'ServerName' => $_SERVER['SERVER_NAME'],
			'ClientIP' => geoip::getClientIP(),
			'ClientAgent' => htmlspecialchars($_SERVER['HTTP_USER_AGENT'])
		);
	}
}