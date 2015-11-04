<?php
class hrsServerLogins_dataClass extends dataClassMysql
{
        
	public static $queries = array(
		self::INSERT => array(
			'type' => 'INSERT',
			'table' => 'serverLogins',
			'tuples' => array(
				'server_id' => 'i',
				'user_id' => 'i',
                                'slave_id' => 'i',
				'ipLong' => 'i',
				'geoip' => 's',
				'sessionKey' => 's',
                                'type' => 's',
			)
		),
	);

}