<?PHP

class db_cache_dataClass extends dataClassMysql
{

	public static $queries = array(
		self::SELECT => array(
			'table' => 'db_cache',
			'type' => 'SELECT',
			'where' => array(
				'cacheKey' => 's',
			),
			'limit_multiplier' => 1,
		),
		self::INSERT => array(
			'table' => 'db_cache',
			'type' => 'INSERT',
			'tuples' => array(
				'cacheKey' => 's',
				'dataString' => 'serial',
				'serverTime' => 'i',
			),
			'on_duplicate' => array(
				'dataString=VALUES(dataString)',
				'serverTime=VALUES(serverTime)',
			),
		),
		self::DELETE => array(
			'table' => 'db_cache',
			'type' => 'DELETE',
			'where' => array(
				'cacheKey' => 's',
			),
			'limit_multiplier' => 1,
		),
	);

}