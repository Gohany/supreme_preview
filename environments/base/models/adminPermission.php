<?PHP
class baseAdminPermissionModel extends model
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
        public $adminPermission_id;
	public $admin_id;
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
	public static $CACHE_KEY = array('admin_id', 'adminPermission_id');
	public static $cacheOwner = array(
		'type' => 'admin',
		'property' => 'admin_id',
	);

	const CACHE_EXPIRATION = 14400;
	const PARENT_MODEL = 'baseAdminPermissionsModel';
        const BASE_MODEL = true;

	public function __construct($data = array())
	{

		parent::__construct($data);
		if (!$this->fromCache)
		{
			if (!$data = $this->read('adminPermissions', array(
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
                
		$permissions['admin_id'] = $permissionsModel->admin_id;

		if (!$adminPermission_id = $permissionsModel->write('adminPermissions', array(
				'action' => 'insert',
				'data' => $permissions
			))
		)
		{
			error::addError('Failed to add permission');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}
                $permissions['adminPermission_id'] = $adminPermission_id;
		$permissions['fromCache'] = true;

		return new baseAdminPermissionModel($permissions);
	}
        
        public function delete()
        {
                if (!$this->write('adminPermissions', array(
				'action' => 'delete',
				'data' => array('adminPermission_id' => $this->adminPermission_id)
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