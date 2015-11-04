<?PHP

class admins_dataClass extends dataClassMysql
{

	public static $queries = array(
		self::SELECT => array(
			'type' => 'SELECT',
			'table' => 'admins',
			'where' => array(
				'admin_id' => 'i',
				'email' => 'e'
			),
			'limit_multiplier' => 1
		),
		self::INSERT => array(
			'type' => 'INSERT',
			'table' => 'admins',
			'tuples' => array(
				'email' => 'e',
                                'database' => 'i',
			)
		),
		self::UPDATE => array(
			'type' => 'UPDATE',
			'table' => 'admins',
			'set' => array(
				'email' => 'e',
                                'database' => 'i',
			),
			'where' => array(
				'admin_id' => 'i'
			),
			'limit_multiplier' => 1
		),
		self::DELETE => array(
			'type' => 'DELETE',
			'table' => 'admins',
			'where' => array(
				'admin_id' => 'i'
			),
			'limit_multiplier' => 1
		)
	);

}