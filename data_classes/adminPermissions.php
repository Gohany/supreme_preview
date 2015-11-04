<?PHP

class adminPermissions_dataClass extends dataClassMysql
{

	public static $queries = array(
		self::SELECT => array(
			'type' => 'SELECT',
			'table' => 'adminPermissions',
			'where' => array(
				'admin_id' => 'i',
				'adminPermission_id' => 'i'
			),
		),
		self::INSERT => array(
			'type' => 'INSERT',
			'table' => 'adminPermissions',
			'tuples' => array(
				'admin_id' => 'i',
				'environment' => 's',
				'controller' => 's',
				'action' => 's',
				'modifyAction' => 's',
			),
		),
		self::UPDATE => array(
			'type' => 'UPDATE',
			'table' => 'adminPermissions',
			'set' => array(
				'environment' => 's',
				'controller' => 's',
				'action' => 's',
				'modifyAction' => 's',
			),
			'where' => array(
				'admin_id' => 'i',
				'environment' => 's',
				'controller' => 's',
				'action' => 's',
				'modifyAction' => 's',
			),
			'limit_multiplier' => 1
		),
                self::DELETE => array(
			'type' => 'DELETE',
			'table' => 'adminPermissions',
			'where' => array(
                                'adminPermission_id' => 'i',
				'admin_id' => 'i',
				'environment' => 's',
				'controller' => 's',
				'action' => 's',
				'modifyAction' => 's',
			),
			'limit_multiplier' => 1
		)
	);

}