<?PHP

class requestGraphs_dataClass extends dataClassMysql
{

	public static $queries = array(
		self::INSERT => array(
			'type' => 'INSERT',
			'table' => 'requestGraphs',
			'default_values' => 1,
			'tuples' => array(
				'currentDate' => 's',
				'hour' => 'i',
				'type' => 's',
				'step' => 'f',
				'number' => 'i',
			),
			'on_duplicate' => array(
			    'number=number+values(number)',
			),
		),
	);

}
