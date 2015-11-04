<?PHP

class hrsAccounts_dataClass extends dataClassMysql
{

	public static $queries = array(
		self::SELECT => array(
			'type' => 'SELECT',
			'table' => 'accounts',
			'where' => array(
                                'account_id' => 'i',
				'user_id' => 'i',
				'email' => 'e'
			),
			'limit_multiplier' => 1
		),
		self::INSERT => array(
			'type' => 'INSERT',
			'table' => 'accounts',
			'tuples' => array(
                                'user_id' => 'i',
				'email' => 'e',
				'firstName' => 's',
				'lastName' => 's',
			)
		),
		self::UPDATE => array(
			'type' => 'UPDATE',
			'table' => 'accounts',
			'set' => array(
				'email' => 'e',
				'firstName' => 's',
				'lastName' => 's',
			),
			'where' => array(
				'account_id' => 'i'
			),
			'limit_multiplier' => 1
		),
		self::DELETE => array(
			'type' => 'DELETE',
			'table' => 'accounts',
			'where' => array(
				'user_id' => 'i'
			),
			'limit_multiplier' => 1
		)
	);

}