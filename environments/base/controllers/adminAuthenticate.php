<?PHP

class adminAuthenticate extends controller
{

        public $baseAdminModel;
        #
        public $properties = array(
                'adminid' => [
                        'FILTER' => FILTER_VALIDATE_INT,
                        'RETURN_FUNCTION' => array(
                                'property' => 'baseAdminModel',
                                'class' => 'baseAdminModel',
                                'method' => 'fromAdmin_id',
                                'includes' => '/environments/base/models/admin.php',
                        )
                ],
                'email' => array(
                        'FILTER' => FILTER_SANITIZE_STRING,
                        'RETURN_FUNCTION' => array(
                                'property' => 'baseAdminModel',
                                'class' => 'baseAdminModel',
                                'method' => 'fromEmail',
                                'includes' => '/environments/base/models/admin.php'
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

                        case baseAdminModel::SRP_ID_ADMINID:
                        case baseAdminModel::SRP_ID_EMAIL:

                                //is account specified?
                                if (empty($this->baseAdminModel))
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
                                $this->baseAdminModel->srpfromPreAuth($this->input['A'], $this->resourceLocation[3]);

                                $this->output = $this->baseAdminModel;
                                break;
                        case baseAdminModel::SRP_ID_EMAIL:
                        case baseAdminModel::SRP_ID_ADMINID:
                                
                                //is account specified?
                                if (empty($this->baseAdminModel))
                                {
                                        throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
                                }
                                
                                $this->baseAdminModel->srpfromPreAuth($this->input['A'], $this->resourceLocation[3]);
                                
                        default:
                                throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
                }
        }

}