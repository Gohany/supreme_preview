<?PHP

class users_dataClass extends dataClassMysql
{

	public static $queries = array(
		self::SELECT => array(
			'type' => 'SELECT',
			'table' => 'users',
			#'joins' => array(
			#	'info' => array(
			#		'using' => 'user_id'
			#	)
			#),
			'where' => array(
				'user_id' => 'i',
				'email' => 'e'
			),
			'limit_multiplier' => 1
		),
		self::INSERT => array(
			'type' => 'INSERT',
			'table' => 'users',
			'tuples' => array(
				'email' => 'e',
				'firstName' => 's',
				'lastName' => 's',
                                'database' => 'i',
			)
		),
		self::UPDATE => array(
			'type' => 'UPDATE',
			'table' => 'users',
			'set' => array(
				'email' => 'e',
				'firstName' => 's',
				'lastName' => 's',
                                'database' => 'i',
			),
			'where' => array(
				'user_id' => 'i'
			),
			'limit_multiplier' => 1
		),
		self::DELETE => array(
			'type' => 'DELETE',
			'table' => 'users',
			'where' => array(
				'user_id' => 'i'
			),
			'limit_multiplier' => 1
		)
	);

}