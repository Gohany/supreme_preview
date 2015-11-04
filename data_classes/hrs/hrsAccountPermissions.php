<?PHP

class hrsAccountPermissions_dataClass extends dataClassMysql
{

	public static $queries = array(
		self::SELECT => array(
			'type' => 'SELECT',
			'table' => 'accountPermissions',
			'where' => array(
				'account_id' => 'i',
				'accountPermission_id' => 'i'
			),
		),
		self::INSERT => array(
			'type' => 'INSERT',
			'table' => 'accountPermissions',
			'tuples' => array(
                                'user_id' => 'i',
				'account_id' => 'i',
				'environment' => 's',
				'controller' => 's',
				'action' => 's',
				'modifyAction' => 's',
			),
		),
		self::UPDATE => array(
			'type' => 'UPDATE',
			'table' => 'accountPermissions',
			'set' => array(
				'environment' => 's',
				'controller' => 's',
				'action' => 's',
				'modifyAction' => 's',
			),
			'where' => array(
                                'user_id' => 'i',
				'account_id' => 'i',
				'environment' => 's',
				'controller' => 's',
				'action' => 's',
				'modifyAction' => 's',
			),
			'limit_multiplier' => 1
		),
                self::DELETE => array(
			'type' => 'DELETE',
			'table' => 'accountPermissions',
			'where' => array(
                                'user_id' => 'i',
                                'accountPermission_id' => 'i',
				'account_id' => 'i',
				'environment' => 's',
				'controller' => 's',
				'action' => 's',
				'modifyAction' => 's',
			),
			'limit_multiplier' => 1
		)
	);

}