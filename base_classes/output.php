<?PHP
class output
{
	const FORMAT_JSON = 'json';
	const FORMAT_PHP_SERIALIZE = 'serialize';

	public static function commit($body = null, $requestTime = null)
	{
		$diagnostics = array();
		if (defined('__DEBUG__') && __DEBUG__)
		{
			if (defined('__DEBUG_DISPLAY_OUTPUT__') && __DEBUG_DISPLAY_OUTPUT__)
			{
				if (ob_get_length() > 0)
				{
					$diagnostics['OutputLog'] = ob_get_contents();
				}
			}
			if (defined('__DEBUG_DISPLAY_QUERY_LOG__') && __DEBUG_DISPLAY_QUERY_LOG__)
			{
				if ($queryLog = dataStore::getArray('__QUERY_ARRAY__', 'queryLog'))
				{
					$diagnostics['Queries'] = $queryLog;
				}
			}
			if (defined('__DEBUG_DISPLAY_EXCEPTIONS__') && __DEBUG_DISPLAY_EXCEPTIONS__)
			{
				if ($exceptionLog = error::getErrorList(true))
				{
					$diagnostics['Exceptions'] = $exceptionLog;
				}
			}
			if (defined('__DEBUG_DISPLAY_INPUT__') && __DEBUG_DISPLAY_INPUT__)
			{
				if (!empty($_REQUEST))
				{
					$diagnostics['Request'] = $_REQUEST;
				}
			}

			if (defined('__DEBUG_DISPLAY_MEMORY__') && __DEBUG_DISPLAY_MEMORY__)
			{
				$peakMemoryUsage = memory_get_peak_usage();
				$memoryUsage = memory_get_usage();

				$diagnostics['Memory'] = array(
					'Peak_Raw' => $peakMemoryUsage,
					'Peak_Human' => round($peakMemoryUsage / 1024 / 1024, 3) . 'MB',
					'Current_Raw' => $memoryUsage,
					'Current_Human' => round($memoryUsage / 1024 / 1024, 3) . 'MB'
				);
			}
		}

		$output = array(
			'meta-data' => dispatcher::getRequestMetaData(),
			'body' => $body
		);

		if (!is_null($requestTime))
		{
			$output['meta-data']['RequestTime'] = $requestTime;
		}

		if ($exceptionLog = error::getErrorList(__DEBUG_VERBOSE_ERRORS__))
		{
			$output['error'] = $exceptionLog;

			if (!empty($exceptionLog) && defined('__DEBUG_LOG_LEVEL__') && __DEBUG_LOG_LEVEL__ > errorCodes::LOG_LEVEL_NONE)
			{
				foreach ($exceptionLog as $errorName)
				{
					$errorLevel = errorCodes::logLevelFromCode(errorCodes::codeFromName($errorName));
					if ($errorLevel <= __DEBUG_LOG_LEVEL__ && $errorLevel > errorCodes::LOG_LEVEL_NONE)
					{
						log::logRequest('Error');
						break;
					}
				}
			}
                        if (!empty($exceptionLog) && !empty($diagnostics['Exceptions']) && defined('__DEBUG_VERBOSE_ERRORS__') && __DEBUG_VERBOSE_ERRORS__)
                        {
                                foreach ($diagnostics['Exceptions'] as $exception)
                                {
                                        print $exception.PHP_EOL;
                                }
                                #print_r($diagnostics);
                                #exit;
                        }
		}

		if (!empty($diagnostics))
		{
			$output['meta-data']['Diagnostics'] = $diagnostics;
		}

		@ob_end_clean();

		if (gc_enabled())
		{
			gc_collect_cycles();
		}

		if (!isset($_SERVER['HTTP_X_SUPREME_OUTPUT_FORMAT']))
		{
			$_SERVER['HTTP_X_SUPREME_OUTPUT_FORMAT'] = self::FORMAT_JSON;
		}
                
		switch (strtolower($_SERVER['HTTP_X_SUPREME_OUTPUT_FORMAT']))
		{
			case self::FORMAT_PHP_SERIALIZE:
				print serialize(view::getOutput($output));
				break;
                        case self::FORMAT_JSON:
                        default:
				print json_encode(view::getOutput($output), JSON_UNESCAPED_UNICODE);
				break;
		}
	}
}