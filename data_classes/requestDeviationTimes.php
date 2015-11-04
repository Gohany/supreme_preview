<?PHP

class requestDeviationTimes_dataClass extends dataClassMysql
{

	public static $queries = array(
		self::SELECT => array(
			'type' => 'SELECT',
			'table' => 'requestDeviationTimes',
			'where' => array(
				'currentTime' => 's',
			),
		),
		self::INSERT => array(
			'type' => 'INSERT',
			'table' => 'requestDeviationTimes',
			'default_values' => 1,
			'tuples' => array(
				'currentTime' => 's',
				'hour' => 'i',
				'type' => 's',
				'requests' => 'i',
				'requestTime' => 'f',
			),
			  'on_duplicate' => array(
			    'requests=requests+values(requests)',
			    'requestTime=requestTime+values(requestTime)',
			),
		),
	);
}
