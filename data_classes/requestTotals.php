<?PHP

class requestTotals_dataClass extends dataClassMysql
{

	public static $queries = array(
		self::INSERT => array(
			'type' => 'INSERT',
			'table' => 'requestTotals',
			'default_values' => 1,
			'tuples' => array(
				'currentDate' => 's',
				'hour' => 'i',
				'type' => 's',
				'requests' => 'i',
				'totalTime' => 'f',
				'totalDeviation' => 'f',
			),
			'on_duplicate' => array(
			    'requests=requests+VALUES(requests)',
			    'totalTime=totalTime+values(totalTime)',
			    'totalDeviation=totalDeviation+values(totalDeviation)'
			),
		),
	);

}
