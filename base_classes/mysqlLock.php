<?PHP
class mysqlLock
{
	public $key;
	public $database;
	public $query;
	public $locked = false;
        public $db_key;

	const MAX_LOCK_TRIES = 10;
	const MAX_LOCK_TIME = 30; //seconds
	const LOCK_TIME = 5;

	public static $lock_types = array('write-read' => array('read', 'write'), 'write' => array('write'));

	public function __construct($key, $database)
	{
                // TODO
                // Key from DATABASE
		$this->key = $key;
		$this->database = $database;
		$this->db_key = $database;
	}

	public function available($type = 'write')
	{
		$tries = 0;
		$lock = $this->locked();

		if ($lock == false)
		{
			return true;
		}

		if (in_array($type, self::$lock_types[$lock]))
		{
			while ($lock != false && in_array($type, self::$lock_types[$lock]))
			{
				usleep(500000);
				$tries++;
				if ($tries >= self::MAX_LOCK_TRIES)
				{
					return false;
				}
				$lock = $this->locked();
			}
		}

		return true;
	}

	public function unlock()
	{
		$query = "DELETE FROM `" . $this->database . "`.`locks` WHERE `lockKey` = '" . mysql::realEscapeString($this->db_key, $this->key) . "' LIMIT 1";
		$this->query = new query($query, $this->db_key, $database);

		if (!$result = $this->query->execute())
		{
			return false;
		}

		$this->locked = false;
		return true;
	}

	public function locked()
	{
		if ($this->locked)
		{
			return $this->locked;
		}

		$query = "SELECT `type`, UNIX_TIMESTAMP(`date_created`) AS `date_created`, unix_timestamp() AS `Current` FROM `" . $this->database . "`.`locks` WHERE `lockKey` = '" . mysql::realEscapeString($this->db_key, $this->key) . "' LIMIT 1";
		$this->query = new query($query, $this->db_key, $this->database);

		/* @var $result['success'] mysqli_result */
		if (!($result = $this->query->execute(array('errno'))) || !$result['success'] || $result['success']->num_rows == 0)
		{
			return false;
		}

		$cache_lock = $result['success']->fetch_assoc();

		if (self::MAX_LOCK_TIME > ($cache_lock['Current'] - $cache_lock['date_created']))
		{
			return isset($cache_lock['type']) ? $cache_lock['type'] : false;
		}

		return false;
	}

	public function lock($type = 'write')
	{
		if (!isset(self::$lock_types[$type]))
		{
			throw new error('Unexpected lock type.');
		}

		$tries = 0;
		$response = false;

		do
		{
			$query = "INSERT INTO `" . $this->database . "`.`locks` (`lockKey`, `type`) VALUES ('" . mysql::realEscapeString($this->db_key, $this->key) . "', '" . mysql::realEscapeString($this->db_key, $type) . "') ON DUPLICATE KEY UPDATE `date_created`=NOW()";
			$this->query = new query($query, $this->db_key, $this->database);

			if (!($result = $this->query->execute()) || !$result['success'])
			{
				usleep(500000);
				$tries++;
				if ($tries >= self::MAX_LOCK_TRIES)
				{
					return false;
				}
			}
			else
			{
				$response = true;
			}
		}
		while (!$response);

		$this->locked = $type;
		return true;
	}
}