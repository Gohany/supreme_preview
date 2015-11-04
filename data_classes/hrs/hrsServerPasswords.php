<?PHP

class hrsServerPasswords_dataClass extends dataClassMysql
{

	public static $queries = array(
		self::SELECT => array(
			'type' => 'SELECT',
			'table' => 'serverPasswords',
			'where' => array(
				'passwordIndex' => 's'
			),
			'limit_multiplier' => 1
		),
		self::INSERT => array(
			'type' => 'INSERT',
			'table' => 'serverPasswords',
			'tuples' => array(
				'passwordIndex' => 's',
				'passwordHash' => 's',
				'salt' => 's'
			),
			'on_duplicate' => array(
				'passwordHash=VALUES(passwordHash)',
				'salt=VALUES(salt)'
			)
		),
		self::UPDATE => array(
			'type' => 'UPDATE',
			'table' => 'serverPasswords',
			'set' => array(
				'passwordHash' => 's',
				'salt' => 's'
			),
			'where' => array(
				'passwordIndex' => 's'
			),
			'limit_multiplier' => 1
		)
	);

}