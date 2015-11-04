<?PHP

class domains_dataClass extends dataClassMysql
{

	public static $queries = array(
		self::SELECT => array(
			'type' => 'SELECT',
			'table' => 'domains',
			'where' => array(
				'user_id' => 'i',
				'name' => 'e'
			),
		),
		self::INSERT => array(
			'type' => 'INSERT',
			'table' => 'domains',
			'tuples' => array(
				'user_id' => 'i',
				'name' => 's',
			)
		),
		self::UPDATE => array(
			'type' => 'UPDATE',
			'table' => 'domains',
			'set' => array(
				'name' => 's',
			),
			'where' => array(
				'domain_id' => 'i'
			),
			'limit_multiplier' => 1
		),
		self::DELETE => array(
			'type' => 'DELETE',
			'table' => 'domains',
			'where' => array(
				'user_id' => 'i',
                                'name' => 's',
			),
			'limit_multiplier' => 1
		)
	);

}