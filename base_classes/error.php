<?PHP

class error extends Exception
{
	public $errors;
	public $output;
	public $statusHeader = 500;
	public $responseHeaders = array();
	public $stackTrace;

	const OBJECT_TYPE = 'error';
	const LOG_DATE_FORMAT = 'H--Y-m-d';

	public function __construct($errorCode = errorCodes::ERROR_INTERNAL_ERROR, $code = 0, Exception $previous = null)
	{
		if (is_int($errorCode))
		{
			$message = errorCodes::messageFromCode($errorCode);
			parent::__construct($message, $errorCode, $previous);
			$this->statusHeader = errorCodes::headerFromCode($errorCode);
		}
		else
		{
			parent::__construct($errorCode, $code, $previous);
		}

		if (extension_loaded('xdebug'))
		{
			$this->stackTrace = array_reverse(xdebug_get_function_stack());
			array_shift($this->stackTrace);
		}

		dataStore::setObject($errorCode, $this, self::OBJECT_TYPE);
	}

	public function getBaseVariables()
	{
		$trace = parent::getTrace();
		foreach ($trace as $traceArray)
		{
			if (isset($traceArray['file'], $traceArray['function']) && basename($traceArray['file']) == 'controller.php' && $traceArray['function'] == 'parseProperties')
			{
				$return = current($traceArray['args']);
			}
		}
		return isset($return) ? $return : false;
	}

	public function getTraceAsStringBetter()
	{
		if (empty($this->stackTrace))
		{
			return parent::getTraceAsString();
		}

		$string = '';
		//Better stack traces from xdebug with collected parameters!
		foreach ($this->stackTrace as $key => $value)
		{
			$string .= "#" . $key . ' ';

			if (count($this->stackTrace) - 1 == $key)
			{
				$string .= $value['function'];
				continue;
			}

			$string .= $value['file'];
			$string .= '(' . $value['line'] . '): ';
			if (isset($value['class']))
			{
				$string .= $value['class'];
				if (isset($value['type']))
				{
					switch ($value['type'])
					{
						case 'static':
							$string .= '::';
							break;
						case 'dynamic':
							$string .= '->';
							break;
					}
				}
			}

			$string .= $value['function'] . '(';
			if (!empty($value['params']))
			{
				$parameters = array();
				foreach ($value['params'] as $paramKey => $paramValue)
				{
					if ($paramValue !== '')
					{
						$parameters[] = $paramValue;
					}
				}
				$string .= implode(', ', $parameters);
			}
			$string .= ')' . PHP_EOL;
		}

		return $string;
	}

	public function __toString()
	{
		$string = PHP_EOL . "ERROR: \t" . $this->getMessage() . PHP_EOL;
		$string .= "CODE: \t" . $this->getCode() . PHP_EOL;
		$string .= "FILE: \t" . $this->getFile() . PHP_EOL;
		$string .= "LINE: \t" . $this->getLine() . PHP_EOL;
		$string .= "TRACE: \t" . str_replace(array(PHP_EOL), array(PHP_EOL . "\t"), $this->getTraceAsStringBetter()) . PHP_EOL;

		return $string . PHP_EOL;
	}

	public static function addError($error, $code = 0)
	{
		return new error($error, $code);
	}

	public static function getErrorList($fullDetail = false)
	{
		$errorList = dataStore::getObjectArray(self::OBJECT_TYPE);
		if ($errorList === false || count($errorList) === 0)
		{
			return false;
		}

		$output = array();
		/* @var $object error */
		foreach ($errorList as $object)
		{
			if ($fullDetail)
			{
				$output[] = strval($object);
			}
			elseif ($object->code != errorCodes::ERROR_DEBUG_INFORMATION)
			{
				$output[] = errorCodes::stringtableFromCode($object->code);
			}
		}

		if (empty($output))
		{
			return false;
		}

		return $output;
	}

	public static function listErrors($ip = false)
	{
		if ($ip && $ip != geoip::getClientIP())
		{
			return true;
		}

		$errors = dataStore::getObjectArray(self::OBJECT_TYPE);
		if ($errors === false)
		{
			return;
		}

		if (is_array($errors) === false)
		{
			$errors = array($errors);
		}

		foreach ($errors as $object)
		{
			echo $object;
		}
	}

	public function output(&$traces = null)
	{
		if (!isset($this->output))
		{
			foreach (dataStore::getObjectArray(self::OBJECT_TYPE) as $object)
			{
				$code = $object->getCode();
				$this->output = stdClass;
				$this->output->errors[] = $code ? $code . " " . $object->getMessage() : $object->getMessage();
				if (isset($traces) && is_array($traces))
				{
					$traces[] = $object->getTrace();
				}
			}
		}

		return $this->output;
	}

	public static function hasErrors()
	{
		$errorList = dataStore::getObjectArray(self::OBJECT_TYPE);

		if ($errorList === false)
		{
			return false;
		}

		foreach ($errorList as $error)
		{
			/* @var $error error */
			//Debug messages don't count as errors
			if ($error->code !== 0)
			{
				return true;
			}
		}

		return false;
	}

	public static function clearErrors()
	{
		dataStore::unsetObjectType(self::OBJECT_TYPE);
	}
}