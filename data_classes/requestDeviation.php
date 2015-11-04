<?PHP

class requestDeviation_dataClass extends dataClassMysql
{

	public static $queries = array(
		self::SELECT => array(
			'type' => 'SELECT',
			'table' => 'requestDeviation',
			'where' => array(
				'currentTime' => 's',
			),
		),
		self::INSERT => array(
			'type' => 'INSERT',
			'table' => 'requestDeviation',
			'default_values' => 1,
			'tuples' => array(
				'type' => 's',
				'path' => 's',
				'input' => 'serial',
				'requestTime' => 'f',
			),
		),
	);
}
