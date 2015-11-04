<?PHP

class authorizeResponse_dataClass extends dataClassMysql
{

	public static $queries = array(
		self::SELECT => array(
			'type' => 'SELECT',
			'table' => 'authorizeResponses',
			'where' => array(
				'invoice_id' => 'i',
			),
			'limit_multiplier' => 1
		),
		self::INSERT => array(
			'type' => 'INSERT',
			'table' => 'authorizeResponses',
			'default_values' => true,
			'tuples' => array(
				'invoice_id' => 'i',
				'response' => 'serial',
			),
		),
		self::UPDATE => array(
			'type' => 'UPDATE',
			'table' => 'authorizeResponses',
			'default_values' => true,
			'set' => array(
				'response' => 'serial',
			),
			'where' => array(
				'invoice_id' => 'i',
			),
			'limit_multiplier' => 1
		),
	);

}