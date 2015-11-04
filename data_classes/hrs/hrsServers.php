<?PHP

class hrsServers_dataClass extends dataClassMysql
{

	public static $queries = array(
		self::SELECT => array(
			'type' => 'SELECT',
			'table' => 'servers',
			'where' => array(
                                'server_id' => 'i',
				'user_id' => 'i',
				'email' => 'e'
			),
			'limit_multiplier' => 1
		),
		self::INSERT => array(
			'type' => 'INSERT',
			'table' => 'servers',
			'tuples' => array(
                                'user_id' => 'i',
                                'slaves' => 'i',
				'email' => 'e',
				'firstName' => 's',
				'lastName' => 's',
			)
		),
		self::UPDATE => array(
			'type' => 'UPDATE',
			'table' => 'servers',
			'set' => array(
                                'slaves' => 'i',
				'email' => 'e',
				'firstName' => 's',
				'lastName' => 's',
			),
			'where' => array(
				'server_id' => 'i'
			),
			'limit_multiplier' => 1
		),
		self::DELETE => array(
			'type' => 'DELETE',
			'table' => 'servers',
			'where' => array(
				'server_id' => 'i'
			),
			'limit_multiplier' => 1
		)
	);

}