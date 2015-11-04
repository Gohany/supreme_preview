<?PHP

class adminPasswords_dataClass extends dataClassMysql
{

	public static $queries = array(
		self::SELECT => array(
			'type' => 'SELECT',
			'table' => 'adminPasswords',
			'where' => array(
				'passwordIndex' => 's'
			),
			'limit_multiplier' => 1
		),
		self::INSERT => array(
			'type' => 'INSERT',
			'table' => 'adminPasswords',
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
			'table' => 'adminPasswords',
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