<?PHP

class hrsServerStatus_dataClass extends dataClassMysql
{

	public static $queries = array(
		self::SELECT => array(
			'type' => 'SELECT',
			'table' => 'serverStatus',
			'where' => array(
                                'server_id' => 'i',
				'user_id' => 'i',
			),
                        'order_by' => 'date_created DESC',
			'limit_multiplier' => 1
		),
		self::INSERT => array(
			'type' => 'INSERT',
			'table' => 'serverStatus',
			'tuples' => array(
                                'server_id' => 'i',
                                'user_id' => 'i',
                                'status' => 'i',
				'localTime' => 'i',
				'actions' => 'i',
			)
		),
	);

}