<?PHP

class adminLog_dataClass extends dataClassMysql
{

	public static $queries = array(
		self::SELECT => array(
			'type' => 'SELECT',
			'table' => 'adminLog',
			'where' => array(
				'admin_id' => 'i',
				'date_created' => 's'
			),
		),
		self::INSERT => array(
			'type' => 'INSERT',
			'table' => 'adminLog',
			'tuples' => array(
				'admin_id' => 'i',
				'adminEnvironment' => 's',
				'environment' => 's',
                                'requester' => 's',
                                'controller' => 's',
                                'action' => 's',
                                'queryString' => 's',
			)
		),
	);

}