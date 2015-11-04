<?PHP

class emailLists_dataClass extends dataClassMysql
{

	public static $queries = array(
		self::SELECT => array(
			'type' => 'SELECT',
			'table' => 'emailLists',
			'where' => array(
				'user_id' => 'i',
				'name' => 'e'
			),
		),
		self::INSERT => array(
			'type' => 'INSERT',
			'table' => 'emailLists',
			'tuples' => array(
				'user_id' => 'i',
				'name' => 's',
			)
		),
		self::UPDATE => array(
			'type' => 'UPDATE',
			'table' => 'emailLists',
			'set' => array(
				'name' => 'e',
			),
			'where' => array(
				'user_id' => 'i'
			),
			'limit_multiplier' => 1
		),
		self::DELETE => array(
			'type' => 'DELETE',
			'table' => 'emailLists',
			'where' => array(
				'user_id' => 'i',
                                'name' => 's',
			),
			'limit_multiplier' => 1
		)
	);

}