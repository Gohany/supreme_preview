<?php
class utility
{
	const DEFAULT_HASH_ALGO = 'sha256';
	const DEFAULT_HMAC_ALGO = 'sha256';
        
	public static $primes = array(2,3,5,7,11,13,17,19,23,29,31,37,41,43,47,53,59,61,67,71,73,79,83,89,97,101,103,107,109,113,127,131,137,139,149,151,157,163,167,173,179,181,191,193,197,199,211,223,227,229,233,239,241,251,257,263,269,271,277,281,283,293,307,311,313,317,331,337,347,349,353,359,367,373,379,383,389,397,401,409,419,421,431,433,439,443,449,457,461,463,467,479,487,491,499,503,509,521,523,541,547,557,563,569,571,577,587,593,599,601,607,613,617,619,631,641,643,647,653,659,661,673,677,683,691,701,709,719,727,733,739,743,751,757,761,769,773,787,797,809,811,821,823,827,829,839,853,857,859,863,877,881,883,887,907,911,919,929,937,941,947,953,967,971,977,983,991,997,1009);

	public static $saltCharacters = array(
		'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
		'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
		'0', '1', '2', '3', '4', '5', '6', '7', '8', '9'
	);
        
        public static function pairFromSemiprime($subPrime)
        {
                foreach (self::$primes as $index => $prime)
                {
                        if ($subPrime % $prime == 0)
                        {
                                return array('multiplier' => $index, 'value' => ($subPrime / $prime));
                        }
                }
        }
        
        public static function subprimeFromPair($multiplier, $value)
        {
                return ($value * self::$primes[$multiplier]);
        }
        
        public static function generatePrime()
        {
                $data = self::getRandomBytes(4);
                $gmp = gmp_abs(gmp_init(bin2hex($data),16));
                return gmp_strval(gmp_nextprime($gmp),10);
        }
        
        public static function pairFromUid($uid)
        {
                $id = $uid >> 8;
                $database = ($uid & 0xFF);
                return array('database' => $database, 'id' => $id);
        }
        
        public static function uidFromPair($database, $id)
        {
                return ($database << 8) | $id;
        }

	public static function addQuotes($string)
	{
		if (is_object($string))
		{
			return "'Object'";
		}
		elseif (is_array($string))
		{
			return "'Array'";
		}

		return "'" . addslashes(strval($string)) . "'";
	}

	public static function hmac($data, $key, $algo = self::DEFAULT_HMAC_ALGO, $raw_output = false)
	{
		return hash_hmac($algo, $data, $key, $raw_output);
	}

	public static function hash($data, $algo = self::DEFAULT_HASH_ALGO, $raw_output = false)
	{
		return hash($algo, $data, $raw_output);
	}

	public static function salt($characters = 22)
	{
		$salt = '';
		for ($i = 0; $i < $characters; $i++)
		{
			$salt .= self::$saltCharacters[self::roll(0, count(self::$saltCharacters) - 1)];
		}
		return !empty($salt) ? $salt : false;
	}

	public static function hashPassword($saltedPassword, $salt)
	{
		#return substr(crypt($password, '$2a$07$'.$salt.'$'),7);
		return hash('sha256', $saltedPassword . $salt);
	}

	public static function fieldTypeCheck($type, &$data)
	{
		switch ($type)
		{
			case 'i':
				if (preg_match('/^-{0,1}\d+$/', $data) !== 1 && !is_null($data))
				{
					error::addError('Expecting int', errorCodes::ERROR_EXPECTATION_FAILED);
					return false;
				}
				return true;
			case 'f':
				if (filter_var($data, FILTER_VALIDATE_FLOAT) === false && !is_null($data))
				{
					error::addError('Expecting float', errorCodes::ERROR_EXPECTATION_FAILED);
					return false;
				}
				return true;
			case 'e':
				if (filter_var($data, FILTER_VALIDATE_EMAIL) === false && !is_null($data))
				{
					error::addError('Expecting email', errorCodes::ERROR_EXPECTATION_FAILED);
					return false;
				}
				return true;
			case 'serial':
				if (!is_string($data) && !is_null($data))
				{
					error::addError('Expecting serialized string', errorCodes::ERROR_EXPECTATION_FAILED);
					return false;
				}
				return true;
			case 'b':
				if (!valid::bool($data))
				{
					return false;
				}
				return true;
			case 'ip':
				if (!valid::ip($data))
				{
					error::addError('Expecting ip address', errorCodes::ERROR_EXPECTATION_FAILED);
					return false;
				}
				return true;
			case 's':
				return true;
		}
	}

	public static function dataValidation(array $data_config, array $data)
	{
		$return = array();
		foreach ($data_config as $key => $config)
		{
			if (!isset($data[$key]) || !isset($config['RETURN_FUNCTION']))
			{
				continue;
			}
			$func = $config['RETURN_FUNCTION'];

			if (isset($func['includes']))
			{
				if (is_string($func['includes']))
				{
					require_once $_SERVER['_HTDOCS_'] . $func['includes'];
				}
				elseif (is_array($func['includes']))
				{
					foreach ($func['includes'] as $include)
					{
						require_once $_SERVER['_HTDOCS_'] . $include;
					}
				}
			}

			$method = $func['method'];
			if (isset($func['class']))
			{
				$method = array($func['class'], $method);
			}

			$args = array($data[$key]);
			if (isset($func['args']))
			{
				$args = array_merge($args, $func['args']);
			}

			if (call_user_func_array($method, $args) !== true)
			{
				if (!isset($config['ERROR_CODE']))
				{
					$config['ERROR_CODE'] = errorCodes::ERROR_DEBUG_INFORMATION;
				}

				$return[] = array('code' => $config['ERROR_CODE'], 'message' => 'NONVALID: ' . $func['method'] . '(' . htmlspecialchars(implode(', ', $args)) . ')');
			}
		}

		return $return;
	}

	public static function roll($min, $max)
	{
		return mt_rand($min, $max);
	}

	public static function isMulti($a)
	{
		foreach ($a as $v)
		{
			if (is_array($v))
			{
				return true;
			}
		}
		return false;
	}

	public static function objectToArray($object)
	{
		if (is_object($object) || gettype($object) == 'object')
		{
			$objectProperties = get_object_vars($object);

			//Need to unset objects, as array_diff can't convert them to strings to compare them
			foreach ($objectProperties as $key => $value)
			{
				if (is_object($value) || gettype($value) == 'object')
				{
					unset($objectProperties[$key]);
				}
			}
			$staticProperties = @array_diff(get_class_vars(get_class($object)), $objectProperties);
			$object = get_object_vars($object);
			if (!empty($staticProperties))
			{
				foreach ($staticProperties as $key => $value)
				{
					$object[$key] = $value;
				}
			}

			if (isset($object['__PHP_Incomplete_Class_Name']))
			{
				unset($object['__PHP_Incomplete_Class_Name']);
			}
		}
		return is_array($object) ? array_map(array(__CLASS__, __FUNCTION__), $object) : $object;
	}

	public static function recursePropertiesFromArray(array $propertyArray, array $filterArray)
	{
		//Fix array values to have named keys
		foreach ($filterArray as $name => $value)
		{
			if (is_int($name) && is_string($value))
			{
				unset($filterArray[$name]);
				$filterArray[$value] = $value;
			}
		}

		$return = array();
		foreach ($propertyArray as $name => $value)
		{
			if (is_null($value))
			{
				continue;
			}

			if (isset($filterArray['*']))
			{
				$filterArray[$name] = $filterArray['*'];
			}
			if (isset($filterArray[$name]))
			{
				if (is_array($filterArray[$name]) && is_array($value))
				{
					$return[$name] = self::recursePropertiesFromArray($value, $filterArray[$name]);
				}
				else
				{
					$return[$name] = $value;
				}
			}
		}

		return $return;
	}

	public static function stringRemoveJunk($var)
	{
		$onIndex = true;
		$onValue = false;
		$index = '';
		$value = '';
		foreach (str_split($var) as $key => $char)
		{
			if ($key === 0 && ($char == '/' || $char == 'ï'))
			{
				return false;
			}

			if (valid::junk($char) === false && $onIndex === true)
			{
				$index .= $char;
			}
			elseif (valid::junk($char) === false && $onIndex === false)
			{
				$onValue = true;
				$value .= $char;
			}
			elseif ($onValue === true && valid::junk($char, true) === false)
			{
				$value .= $char;
			}
			elseif ($onIndex === true && valid::junk($char) === true)
			{
				$onIndex = false;
			}
		}

		if (!empty($index) && !empty($value))
		{
			return array($index => trim($value));
		}

		return false;
	}

	/**
	 * Convert from string to bool (by reference) in a standard way.
	 * @param string $str
	 * @return bool
	 */
	public static function stringToBool(&$str)
	{
		$str = isset($str) && ($str === true || $str == 1 || $str == 'true');
		return $str;
	}

	/**
	 * Randomly pick an index from $probabilities, weighted by the values
	 * @param array $probabilities
	 * @return int
	 */
	public static function weightedRandom(array $probabilities)
	{
		if (empty($probabilities))
		{
			return false;
		}

		$rand = mt_rand() * array_sum($probabilities) / mt_getrandmax();
		$sum = 0;
		foreach ($probabilities as $i => $weight)
		{
			$sum += $weight;
			if ($sum >= $rand)
			{
				return $i;
			}
		}
	}

	/**
	 * Replace occurences of $search with ascending elements from $replaceArray
	 * @param array $search
	 * @param string $replaceArray
	 * @param mixed $subject Keep elements with a field == $value
	 * @return array
	 */
	public static function str_replace_array($search, $replaceArray, $subject)
	{
		$str = str_replace(array('%', $search), array('%%', '%s'), $subject);
		return vsprintf($str, $replaceArray);
	}

	/**
	 * Removes K2 color codes from a string.
	 * @param type $message
	 * @return type
	 */
	public static function stripColorCodes($message)
	{
		$colorCodes = array_map('preg_quote', array_keys(self::$colorCodeTable));

		return preg_replace('/\^[0-9][0-9][0-9]|' . implode('|', $colorCodes) . '/', '', $message);
	}

	/**
	 * Returns random bytes.
	 * @param int $bytes
	 * @return string
	 */
	public static function getRandomBytes($bytes)
	{
		if (is_readable('/dev/urandom'))
		{
			$handle = @fopen('/dev/urandom', 'rb');
			if ($handle)
			{
				$data = '';
				do
				{
					$data .= fread($handle, $bytes);
				}
				while (strlen($data) < $bytes);

				fclose($handle);

				return substr($data, 0, $bytes);
			}
		}

		return openssl_random_pseudo_bytes($bytes);
	}

	public static function getRandomHexBytes($bytes)
	{
		return bin2hex(self::getRandomBytes(ceil($bytes / 2)));
	}

	public static function reduceAccentedCharacters($string)
	{
		return strtr($string, array(
			'Š' => 'S', 'š' => 's', 'Đ' => 'D', 'đ' => 'd', 'Ž' => 'Z', 'ž' => 'z', 'Č' => 'C', 'č' => 'c', 'Ć' => 'C', 'ć' => 'c',
			'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
			'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O',
			'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'P', 'ß' => 'B',
			'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c', 'è' => 'e', 'é' => 'e',
			'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o',
			'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'ý' => 'y', 'þ' => 'b',
			'ÿ' => 'y', 'Ŕ' => 'R', 'ŕ' => 'r', '0' => 'o'
		));
	}

	private static function getBadWordList()
	{
		if (is_null(self::$badWordList))
		{
			self::$badWordList = explode("\n", file_get_contents($_SERVER['_HTDOCS_'] . '/configs/bad_wordlist.txt'));
		}

		return self::$badWordList;
	}

	public static function badWordFilter($string)
	{
		$badWordList = self::getBadWordList();
		if (empty($badWordList))
		{
			return $string;
		}

		foreach ($badWordList as $badWord)
		{
			$string = preg_replace('/(^|[\p{P}\s\d])(' . $badWord . ')($|[\p{P}\s\d])/i', '\1' . str_pad('', strlen($badWord), '*') . '\3', $string);
		}

		return $string;
	}

	public static function isDir($dir, $mkdir = false, $recursive = false)
	{
		$dir = trim($dir, '/');
		if (isset($_SERVER['_REPLAY_CACHE_DIR_']))
		{
			$localDir = $_SERVER['_REPLAY_CACHE_DIR_'];
		}
		else
		{
			$localDir = '/var/www/downloads';
		}

		if (@chdir($dir))
		{
			@chdir($localDir);
			return true;
		}

		if (!$mkdir)
		{
			return false;
		}

		$explode = explode('/', trim($dir, '/'));
		if (!$recursive)
		{
			return mkdir(end($explode));
		}

		for ($i = 0, $c = count($explode); $i < $c; ++$i)
		{
			if (!@chdir($explode[$i]))
			{
				if (mkdir($explode[$i]))
				{
					if (chdir($explode[$i]))
					{
						continue;
					}
					else
					{
						break;
					}
				}
				else
				{
					break;
				}
			}
		}

		@chdir($localDir);
		return (self::isDir($dir));
	}

	public static function getAllPermissions($clientTypeFilter = null)
	{
		$permissionMap = array();
		foreach (glob($_SERVER['_HTDOCS_'] . '/environments/*') as $environmentPath)
		{
			$environment = basename($environmentPath);
			if (is_file($environmentPath . '/permissions/' . $environment . 'Permissions.php'))
			{
				require_once $environmentPath . '/permissions/' . $environment . 'Permissions.php';
			}

			foreach (glob($environmentPath . '/controllers/*\.php') as $controllerPath)
			{
				$controller = substr(basename($controllerPath), 0, -4);
				if (!is_file($environmentPath . '/permissions/' . $controller . 'Permissions.php'))
				{
					continue;
				}

				$permissions = null;
				require $environmentPath . '/permissions/' . $controller . 'Permissions.php';
				if (is_null($permissions) || !is_array($permissions))
				{
					continue;
				}

				foreach ($permissions as $action => $clientTypeList)
				{
					if (!is_null($clientTypeFilter) && !isset($clientTypeList[$clientTypeFilter]))
					{
						continue;
					}

					if ($action == 'modify')
					{
						if (!is_null($clientTypeFilter))
						{
							foreach ($clientTypeList[$clientTypeFilter] as $actionName => $actionData)
							{
								$permissionMap[$environment][$controller][$action][$actionName] = false;
							}
						}
						else
						{
							foreach ($clientTypeList as $modifyAction)
							{
								foreach ($modifyAction as $actionName => $actionData)
								{
									$permissionMap[$environment][$controller][$action][$actionName] = false;
								}
							}
						}
					}
					elseif ($action == 'create')
					{
						if (!is_null($clientTypeFilter))
						{
							foreach ($clientTypeList[$clientTypeFilter] as $actionName => $actionData)
							{
								if ($actionName != 'model' && $actionName != 'validation')
								{
									$permissionMap[$environment][$controller][$action][$actionName] = false;
								}
								else
								{
									$permissionMap[$environment][$controller][$action] = false;
								}
							}
						}
						else
						{
							foreach ($clientTypeList as $modifyAction)
							{
								foreach ($modifyAction as $actionName => $actionData)
								{
									if ($actionName != 'model' && $actionName != 'validation')
									{
										$permissionMap[$environment][$controller][$action][$actionName] = false;
									}
									else
									{
										$permissionMap[$environment][$controller][$action] = false;
									}
								}
							}
						}
					}
					else
					{
						$permissionMap[$environment][$controller][$action] = false;
					}
				}
			}
		}
		return $permissionMap;
	}

	/**
	 * Returns random alpha numeric string of length.
	 * @param int $length
	 * @return string
	 */
	public static function getRandomAlphaNumeric($length)
	{
		$key = '';
		$length = intval($length);

		do
		{
			$randomBytes = bin2hex(openssl_random_pseudo_bytes(20));

			$key .= gmp_strval(gmp_init($randomBytes, 16), 36);
		}
		while (strlen($key) < $length);

		return substr($key, 0, $length);
	}

	public static function getHex($data)
	{
		echo implode(' ', str_split(bin2hex($data), 2));
	}

	public static function printHexDump($data, $width = 16, $padding = '.', $newline = "\n")
	{
		$from = '';
		$to = '';
		$offset = 0;

		for ($i = 0; $i <= 0xFF; ++$i)
		{
			$from .= chr($i);
			$to .= ($i >= 0x20 && $i <= 0x7E) ? chr($i) : $padding;
		}

		$chars = str_split(strtr($data, $from, $to), $width);
		foreach (str_split(bin2hex($data), $width * 2) as $i => $line)
		{
			echo sprintf('%6d', $offset) . ' : ' . str_pad(implode(' ', str_split($line, 2)), ($width * 3 - 1), ' ') . ' [' . str_pad($chars[$i], $width) . ']' . $newline;
			$offset += $width;
		}
	}

	/**
	 * Extract all of $key into an array.  PHP5.5 has a better version of this in a function called array_column.
	 * We use PHP5.4.  Oh well.
	 *
	 * @param array $array
	 * @param string $key
	 * @return array
	 */
	public static function extractKeyFromArray(array $array, $key, $index = null)
	{
		$return = array();
		foreach ($array as $valueList)
		{
			if (!isset($valueList[$key]))
			{
				continue;
			}

			if ($index === null || !isset($valueList[$index]))
			{
				$return[] = $valueList[$key];
			}
			else
			{
				$return[$valueList[$index]] = $valueList[$key];
			}
		}
		return $return;
	}
}