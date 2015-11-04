<?PHP
class hrsAccountPermissionModel extends model
{
        ##
        # Constants
        ##
        const ENVIRONMENT = 'environment';
        const CONTROLLER = 'controller';
        const ACTION = 'action';
        const MODIFY_ACTION = 'modifyAction';
        
	##
	# Properties
	##
        public $accountPermission_id;
	public $account_id;
        public $user_id;
	public $environment;
	public $controller;
	public $action;
        public $modifyAction = null;
        
        public static $permissions = [
            self::ENVIRONMENT,
            self::CONTROLLER,
            self::ACTION,
            self::MODIFY_ACTION,
        ];
        
	##
	# Cache
	##
	public static $CACHE_KEY = array('account_id', 'accountPermission_id');
	public static $cacheOwner = array(
		'type' => 'account',
		'property' => 'account_id',
	);

        const OWNER_KEY = 'user_id';
        const BASE_DB = 'hrs';
	const CACHE_EXPIRATION = 14400;
	const PARENT_MODEL = 'hrsAccountPermissionsModel';

	public function __construct($data = array())
	{

		parent::__construct($data);
		if (!$this->fromCache)
		{
			if (!$data = $this->read('hrsAccountPermissions', array(
					'data' => $data,
					'limit' => 1
				))
			)
			{
                                throw new error(errorCodes::ERROR_ACCOUNT_NOT_FOUND);
			}
                        
		}
                
                $this->loadProperties($data);
                
	}

	public static function create($permissionsModel, $permissions)
	{
                
                if (array_keys($permissions) != baseAdminPermissionModel::$permissions)
                {
                        return false;
                }
                
		$permissions['account_id'] = $permissionsModel->account_id;

		if (!$accountPermission_id = $permissionsModel->write('hrsAccountPermissions', array(
				'action' => 'insert',
				'data' => $permissions
			))
		)
		{
			error::addError('Failed to add permission');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}
                $permissions['accountPermission_id'] = $accountPermission_id;
		$permissions['fromCache'] = true;

		return new hrsAccountPermissionModel($permissions);
	}
        
        public function delete()
        {
                if (!$this->write('hrsAccountPermissions', array(
				'action' => 'delete',
				'data' => array('accountPermission_id' => $this->accountPermission_id)
			))
		)
		{
			error::addError('Failed to remove permission');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}
                $this->deleteCache();
                return true;
        }
}