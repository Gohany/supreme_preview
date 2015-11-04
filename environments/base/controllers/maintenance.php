<?php
class maintenance extends controller
{
	const STATUS_ACTIVE = 'active';
	const STATUS_MAINTENANCE = 'maintenance';
	const REDIS_KEY = 'turnOffLogins';

	public $properties = array();
	public $create_data = array();

	public function display()
	{
		$ipList = false;

		$redis = RedisPool::getRedisKey(self::REDIS_KEY);
		$data = $redis->get(redisKey::DATABASE_META);
		if ($data !== false)
		{
			$ipList = unserialize($data);
		}

		if ($this->X_requester != dispatcher::REQUESTER_ADMIN)
		{
			$ipList = (is_array($ipList));
		}

		$this->output = $ipList;
	}

	public function create()
	{
		//Check Our ip list.
		$ipArray = explode(',', $this->input['ipList']);
		foreach ($ipArray as $key => $ip)
		{
			$ip = trim($ip);
			$ipArray[$key] = $ip;
			if (!valid::ip($ip))
			{
				error::addError('Invalid IP: ' . $ip);
				throw new error(errorCodes::ERROR_INVALID_IP);
			}
		}

		$redis = RedisPool::getRedisKey(self::REDIS_KEY);
		$redis->set(serialize($ipArray), array(), redisKey::DATABASE_META);

		$this->output = $redis->get(redisKey::DATABASE_META);
	}

	public function delete()
	{
		$redis = RedisPool::getRedisKey(self::REDIS_KEY);
		$redis->delete(redisKey::DATABASE_META);

		$this->output = true;
	}
}