<?php
class logins_dataClass extends dataClassMysql
{
        
	public static $queries = array(
		self::INSERT => array(
			'type' => 'INSERT',
			'table' => 'logins',
			'tuples' => array(
				'user_id' => 'i',
				'ipLong' => 'i',
				'geoip' => 's',
				'sessionKey' => 's',
                                'type' => 's',
			)
		),
	);

}