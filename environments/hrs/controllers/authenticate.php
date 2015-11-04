<?PHP

class authenticate extends controller
{

        public $hrsAccountModel;
        public $ownerModel;
	const PRIMARY_MODEL = 'hrsAccountModel';
        
        public $properties = array(
                'uid' => [
			'FILTER' => FILTER_VALIDATE_FLOAT,
			'RETURN_FUNCTION' => [
				'property' => self::PRIMARY_MODEL,
				'class' => self::PRIMARY_MODEL,
				'method' => 'fromUid',
				'includes' => '/environments/hrs/models/account.php',
			],
		],
                'email' => [
			'FILTER' => FILTER_SANITIZE_STRING,
			'RETURN_FUNCTION' => [
				'property' => self::PRIMARY_MODEL,
				'class' => self::PRIMARY_MODEL,
				'method' => 'fromData',
				'includes' => '/environments/hrs/models/account.php',
                                'stringLookup' => 'email',
			],
		],
        );

        public function create()
        {
                require_once $_SERVER['_HTDOCS_'] . '/base_classes/utility/srp.php';

                if (empty($this->resourceLocation[3]))
                {
                        error::addError("Resource must be specified.");
                        throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
                }

                if (empty($this->input['A']))
                {
                        error::addError("Missing 'A'");
                        throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
                }

                if (!valid::hex($this->input['A']))
                {
                        error::addError("Bad 'A'; should be hex value.");
                        throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
                }

                $this->resourceLocation[3] = strtolower($this->resourceLocation[3]);
                
                switch ($this->resourceLocation[3])
                {

                        case hrsAccountModel::SRP_ID_INDEX:
                        case hrsAccountModel::SRP_ID_EMAIL:
                        case hrsAccountModel::SRP_ID_UID:

                                //is account specified?
                                if (empty($this->{self::PRIMARY_MODEL}))
                                {
                                        throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
                                }


                                //Maintenance Mode Check
                                {
                                        require_once $_SERVER['_HTDOCS_'] . '/environments/base/controllers/maintenance.php';

                                        $redis = RedisPool::getRedisKey(maintenance::REDIS_KEY);
                                        $ipList = $redis->get(redisKey::DATABASE_META);
                                        if ($ipList !== false)
                                        {
                                                $ipList = unserialize($ipList);
                                                if (is_array($ipList) && !in_array($_SERVER['REMOTE_ADDR'], $ipList))
                                                {
                                                        throw new error(errorCodes::ERROR_MAINTENANCE);
                                                }
                                        }
                                }

                                //Do login
                                $this->{self::PRIMARY_MODEL}->srpfromPreAuth($this->input['A'], $this->resourceLocation[3]);

                                $this->output = $this->{self::PRIMARY_MODEL};
                                break;
                        default:
                                throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
                }
        }

}