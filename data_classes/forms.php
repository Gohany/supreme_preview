<?PHP

class forms_dataClass extends dataClassMysql
{

	public static $queries = array(
		self::SELECT => array(
			'type' => 'SELECT',
			'table' => 'forms',
			'where' => array(
				'user_id' => 'i',
				'name' => 'e'
			),
		),
		self::INSERT => array(
			'type' => 'INSERT',
			'table' => 'forms',
			'tuples' => array(
                                'domain_id' => 'i',
				'user_id' => 'i',
				'name' => 's',
                                'data' => 's',
			)
		),
		self::UPDATE => array(
			'type' => 'UPDATE',
			'table' => 'forms',
			'set' => array(
				'name' => 'e',
                                'data' => 's',
			),
			'where' => array(
				'form_id' => 'i'
			),
			'limit_multiplier' => 1
		),
		self::DELETE => array(
			'type' => 'DELETE',
			'table' => 'forms',
			'where' => array(
				'form_id' => 'i',
			),
			'limit_multiplier' => 1
		)
	);

}