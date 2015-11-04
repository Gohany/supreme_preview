<?PHP

class authenticate extends controller
{

        public $baseUserModel;
        #
        public $properties = array(
                'userid' => array(
                        'FILTER' => FILTER_VALIDATE_INT,
                        'RETURN_FUNCTION' => array(
                                'property' => 'baseUserModel',
                                'class' => 'baseUserModel',
                                'method' => 'fromUser_id',
                                'includes' => '/environments/base/models/user.php'
                        )
                ),
                'email' => array(
                        'FILTER' => FILTER_SANITIZE_STRING,
                        'RETURN_FUNCTION' => array(
                                'property' => 'baseUserModel',
                                'class' => 'baseUserModel',
                                'method' => 'fromEmail',
                                'includes' => '/environments/base/models/user.php'
                        )
                ),
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

                        case baseUserModel::SRP_ID_INDEX:
                        case baseUserModel::SRP_ID_EMAIL:

                                //is account specified?
                                if (empty($this->baseUserModel))
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
                                $this->baseUserModel->srpfromPreAuth($this->input['A'], $this->resourceLocation[3]);

                                $this->output = $this->baseUserModel;
                                break;
                        default:
                                throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
                }
        }

}