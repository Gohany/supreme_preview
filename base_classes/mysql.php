<?PHP
class mysql
{
	const OBJECT_TYPE = 'mysqli';

	public static $instance;
	public $mysqli;

	public function __construct($key)
	{
		if (!file_exists($_SERVER['_HTDOCS_'] . '/configs/database/mysql/' . $key . '.php'))
		{
			error::addError('Connection file does not exist for "' . $key . '"');
			throw new error(errorCodes::ERROR_DATABASE_NOT_FOUND);
		}

		require $_SERVER['_HTDOCS_'] . '/configs/database/mysql/' . $key . '.php';

		if (defined('__MYSQL_PERSISTENT__') && __MYSQL_PERSISTENT__)
		{
			$host = 'p:' . $host;
		}

		if (!extension_loaded('mysqli'))
		{
			error::addError('Missing PHP mysqli extension');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		$this->mysqli = @new mysqli($host, $user, $password, null, $port, $socket);
		if (!is_null($this->mysqli->connect_error))
		{
			error::addError('MySQL Error #' . $this->mysqli->connect_errno . ': ' . $this->mysqli->connect_error);
			throw new error(errorCodes::ERROR_COULD_NOT_CONNECT_TO_DATABASE);
		}

		$this->mysqli->set_charset("utf8");
	}

	public function __destruct()
	{
		self::destroyConnections();
	}

	public static function destroyConnections()
	{
		if ($objects = dataStore::getObjectArray(self::OBJECT_TYPE))
		{
			foreach ($objects as $object)
			{
				$object->mysqli->close();
			}

			dataStore::unsetObjectType(self::OBJECT_TYPE);
		}
	}

	public static function singleton($key)
	{
		if (!(self::$instance = dataStore::getObject($key, self::OBJECT_TYPE)))
		{
			self::$instance = new mysql($key);
			dataStore::setObject($key, self::$instance, self::OBJECT_TYPE);
		}
		return self::$instance->mysqli;
	}

	public static function linkIdentifier($key)
	{
		return self::singleton($key);
	}

	public static function affectedRows($key)
	{
		return self::singleton($key)->affected_rows;
	}

	public static function autoCommit($key, $mode = true)
	{
		return self::singleton($key)->autocommit($mode);
	}

	public static function changeUser($key, $user, $password, $database)
	{
		return self::singleton($key)->change_user($user, $password, $database);
	}

	public static function characterSetName($key)
	{
		return self::singleton($key)->character_set_name();
	}

	public static function clientInfo($key)
	{
		return self::singleton($key)->client_info;
	}

	public static function clientVersion($key)
	{
		return self::singleton($key)->client_version;
	}

	public static function commit($key)
	{
		return self::singleton($key)->commit();
	}

	public static function connectErrno($key)
	{
		return self::singleton($key)->connect_errno;
	}

	public static function connectError($key)
	{
		return self::singleton($key)->connect_error;
	}

	public static function debug($key, $message)
	{
		return self::singleton($key)->debug($message);
	}

	public static function dumpDebugInfo($key)
	{
		return self::singleton($key)->dump_debug_info();
	}

	public static function errno($key)
	{
		return self::singleton($key)->errno;
	}

	public static function errorList($key)
	{
		return self::singleton($key)->error_list;
	}

	public static function error($key)
	{
		return self::singleton($key)->error;
	}

	public static function fieldCount($key)
	{
		return self::singleton($key)->field_count;
	}

	public static function getCharset($key)
	{
		return self::singleton($key)->get_charset();
	}

	public static function getClientInfo($key)
	{
		return self::singleton($key)->get_client_info();
	}

	public static function getClientStats($key)
	{
		return self::singleton($key)->getClientStats();
	}

	public static function getConnectionStats($key)
	{
		return self::singleton($key)->get_connection_stats();
	}

	public static function hostInfo($key)
	{
		return self::singleton($key)->host_info;
	}

	public static function protocolVersion($key)
	{
		return self::singleton($key)->protocol_version;
	}

	public static function serverInfo($key)
	{
		return self::singleton($key)->server_info;
	}

	public static function serverVersion($key)
	{
		return self::singleton($key)->server_version;
	}

	public static function getWarnings($key)
	{
		return self::singleton($key)->get_warnings();
	}

	public static function info($key)
	{
		return self::singleton($key)->info;
	}

	public static function init($key)
	{
		return self::singleton($key)->init();
	}

	public static function insertId($key)
	{
		return self::singleton($key)->insert_id;
	}

	public static function kill($key, $pid)
	{
		return self::singleton($key)->kill($pid);
	}

	public static function moreResults($key)
	{
		return self::singleton($key)->more_results();
	}

	public static function multiQuery($key, $query)
	{
		return self::singleton($key)->multi_query($query);
	}

	public static function nextResult($key)
	{
		return self::singleton($key)->next_result();
	}

	public static function options($key)
	{
		return self::singleton($key)->options;
	}

	public static function ping($key)
	{
		return self::singleton($key)->ping();
	}

	public static function poll($key, $read, $error, $reject, $sec = 1, $usec = 1000000)
	{
		return self::singleton($key)->poll($read, $error, $reject, $sec, $usec);
	}

	public static function prepare($key, $query)
	{
		return query::fromPrepare(self::singleton($key), $query);
	}

	/**
	 * Returns false on mysql failure, else a mysqli_result
	 *
	 * @param string $key
	 * @param string $query
	 * @param int $resultMode
	 * @return mysqli_result|boolean
	 */
	public static function query($key, $query, $resultMode = MYSQLI_STORE_RESULT)
	{
		return self::singleton($key)->query($query, $resultMode);
	}

	public static function realEscapeString($key, $string)
	{
		return self::singleton($key)->real_escape_string($string);
	}

	public static function realQuery($key, $query)
	{
		return self::singleton($key)->real_query($query);
	}

	public static function reapAsyncQuery($key)
	{
		return self::singleton($key)->reap_async_query();
	}

	public static function refresh($key, $options)
	{
		return self::singleton($key)->refresh($options);
	}

	public static function rollBack($key)
	{
		return self::singleton($key)->rollback();
	}

	public static function selectDB($key, $dbName)
	{
		return self::singleton($key)->select_db($dbName);
	}

	public static function setCharset($key, $charset = 'utf8')
	{
		return self::singleton($key)->set_charset($charset);
	}

	public static function sqlState($key)
	{
		return self::singleton($key)->sqlstate;
	}

	public static function sslSet($key, $keyFilePath, $cert, $ca, $capath, $cipher)
	{
		return self::singleton($key)->ssl_set($keyFilePath, $cert, $ca, $capath, $cipher);
	}

	public static function stat($key)
	{
		return self::singleton($key)->stat();
	}

	public static function storeResult($key)
	{
		return self::singleton($key)->store_result();
	}

	public static function threadId($key)
	{
		return self::singleton($key)->thread_id;
	}

	public static function threadSafe($key)
	{
		return self::singleton($key)->thread_safe();
	}

	public static function useResult($key)
	{
		return self::singleton($key)->use_result();
	}

	public static function warningCount($key)
	{
		return self::singleton($key)->warning_count;
	}
}