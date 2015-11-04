<?PHP
class hrsAccountPermissionsModel extends model
{
        

	##
	# Properties
	##
	public $account_id;
        public $user_id;
	public $permissions = array();
        
//        public static $_CONFIGS = array(
//            'permissionSet1',
//            'permissionSet2',
//            'permissionSet3',
//        );
        
        public static $permissionSet1;
        public static $permissionSet2;
        public static $permissionSet3;
        

	##
	# Cache
	##
	public static $CACHE_KEY = array('account_id');
	public static $COLLECTIONS = array('permissions');
	public static $cacheOwner = array(
		'type' => 'account',
		'property' => 'account_id',
	);

        const OWNER_KEY = 'user_id';
        const BASE_DB = 'hrs';
	const CACHE_EXPIRATION = 14400;
	const PARENT_MODEL = 'hrsAccountModel';

	public function __construct($data = array())
	{
		require_once $_SERVER['_HTDOCS_'] . '/environments/hrs/models/accountPermission.php';

		parent::__construct($data);
		if (!$this->fromCache)
		{
			if ($permissionList = $this->read('hrsAccountPermissions', array(
					'data' => array(
						'account_id' => $data['account_id']
					)
				))
			)
			{
				foreach ($permissionList as $permission)
				{
					$permission['database'] = $this->database;
					$permission['fromCache'] = true;

					$permissionModel = new hrsAccountPermissionModel($permission);
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
		$permission = hrsAccountPermissionModel::create($this, $permissions);
		$this->updatePermissions($permission);

		return $permission;
	}

	/**
	 *
	 * @param hrsAccountPermissionModel $permission
	 */
	public function remove(hrsAccountPermissionModel $permission)
	{
                $permission->delete();
		unset($this->permissions[$permission->environment . ':' . $permission->controller . ':' . $permission->action . ':' . $permission->modifyAction]);
		return true;
	}
        
	/**
	 *
	 * @param hrsAccountPermissionModel $permission
	 */
	public function updatePermissions(hrsAccountPermissionModel $permission)
	{
		$this->permissions[$permission->environment . ':' . $permission->controller . ':' . $permission->action . ':' . $permission->modifyAction] = $permission;
		return true;
	}

	public function permissionByArray($array)
	{
                
		if (array_keys($array) != hrsAccountPermissionModel::$permissions)
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