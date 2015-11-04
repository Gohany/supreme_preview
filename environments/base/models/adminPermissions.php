<?PHP
class baseAdminPermissionsModel extends model
{
        

	##
	# Properties
	##
	public $admin_id;
	public $permissions = array();
        
        public static $_CONFIGS = array(
            'permissionSet1',
            'permissionSet2',
            'permissionSet3',
        );
        
        public static $permissionSet1;
        public static $permissionSet2;
        public static $permissionSet3;

	##
	# Cache
	##
	public static $CACHE_KEY = array('admin_id');
	public static $COLLECTIONS = array('permissions');
	public static $cacheOwner = array(
		'type' => 'admin',
		'property' => 'admin_id',
	);

	const CACHE_EXPIRATION = 14400;
	const PARENT_MODEL = 'baseAdminModel';
        const BASE_MODEL = true;

	public function __construct($data = array())
	{
		require_once $_SERVER['_HTDOCS_'] . '/environments/base/models/adminPermission.php';

		parent::__construct($data);
		if (!$this->fromCache)
		{
			if ($permissionList = $this->read('adminPermissions', array(
					'data' => array(
						'admin_id' => $data['admin_id']
					)
				))
			)
			{
				foreach ($permissionList as $permission)
				{
					$permission['database'] = $this->database;
					$permission['fromCache'] = true;

					$permissionModel = new baseAdminPermissionModel($permission);
                                        $this->updatePermissions($permissionModel);
				}
			}
		}
	}
        
        public function addPermissionSet($permissionSet)
        {
                if (empty(self::${$permissionSet}))
                {
                        throw new error(errorCodes::ERROR_BAD_CONFIG_DATA);
                }
                
                foreach (self::${$permissionSet} as $permission)
                {
                        if (!$this->permissionByArray($permission))
                        {
                                $this->add($permission);
                        }
                }
                return true;
        }

	public function add($permissions)
	{
		require_once $_SERVER['_HTDOCS_'] . '/environments/base/models/session.php';
		$permission = baseAdminPermissionModel::create($this, $permissions);
		$this->updatePermissions($permission);

		return $permission;
	}

	/**
	 *
	 * @param baseAdminPermissionModel $permission
	 */
	public function remove(baseAdminPermissionModel $permission)
	{
                $permission->delete();
		unset($this->permissions[$permission->environment . ':' . $permission->controller . ':' . $permission->action . ':' . $permission->modifyAction]);
		return true;
	}
        
	/**
	 *
	 * @param baseAdminPermissionModel $permission
	 */
	public function updatePermissions(baseAdminPermissionModel $permission)
	{
		$this->permissions[$permission->environment . ':' . $permission->controller . ':' . $permission->action . ':' . $permission->modifyAction] = $permission;
		return true;
	}

	public function permissionByArray($array)
	{
                
		if (array_keys($array) != baseAdminPermissionModel::$permissions)
                {
                        return false;
                }
                
		if (isset($this->permissions[$array['environment'] . ':' . $array['controller'] . ':' . $array['action'] . ':' . $array['modifyAction']]))
                {
                        return $this->permissions[$array['environment'] . ':' . $array['controller'] . ':' . $array['action'] . ':' . $array['modifyAction']];
                }
                return false;
	}
}