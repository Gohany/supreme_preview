<?php
class hrsServerSessions_dataClass extends dataClassMysql
{

	public static $serializedFields = array('clientData', 'sessionData');
	public static $queries = array(
		self::SELECT => array(
			'type' => 'SELECT',
			'table' => 'serverSessions',
			'where' => array(
				'server_id' => 'i',
                                'slave_id' => 'i',
				'sessionKey' => 's',
			)
		),
		self::INSERT => array(
			'type' => 'INSERT',
			'table' => 'serverSessions',
			'tuples' => array(
				'server_id' => 'i',
                                'slave_id' => 'i',
				'user_id' => 'i',
				'sessionKey' => 's',
				'type' => 's',
				'ip' => 'i',
				'clientData' => 's',
				'expiration' => 's',
				'sessionData' => 's',
			)
		),
		self::UPDATE => array(
			'type' => 'UPDATE',
			'table' => 'serverSessions',
			'set' => array(
				'expiration' => 's',
				'sessionData' => 's'
			),
			'where' => array(
				'user_id' => 'i',
				'sessionKey' => 's'
			)
		),
		self::DELETE => array(
			'type' => 'DELETE',
			'table' => 'serverSessions',
			'where' => array(
				'server_id' => 'i',
				'sessionKey' => 's'
			)
		)
	);

}