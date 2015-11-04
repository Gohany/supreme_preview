<?php
class adminLogins_dataClass extends dataClassMysql
{
        
	public static $queries = array(
		self::INSERT => array(
			'type' => 'INSERT',
			'table' => 'adminLogins',
			'tuples' => array(
				'admin_id' => 'i',
				'ipLong' => 'i',
				'geoip' => 's',
				'sessionKey' => 's',
                                'type' => 's',
			)
		),
	);

}