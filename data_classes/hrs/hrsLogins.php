<?php
class hrsLogins_dataClass extends dataClassMysql
{
        
	public static $queries = array(
		self::INSERT => array(
			'type' => 'INSERT',
			'table' => 'logins',
			'tuples' => array(
				'account_id' => 'i',
				'user_id' => 'i',
				'ipLong' => 'i',
				'geoip' => 's',
				'sessionKey' => 's',
                                'type' => 's',
			)
		),
	);

}