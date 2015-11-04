<?php
class adminSessions_dataClass extends dataClassMysql
{

	public static $serializedFields = array('clientData', 'sessionData');
	public static $queries = array(
		self::SELECT => array(
			'type' => 'SELECT',
			'table' => 'adminSessions',
			'where' => array(
				'admin_id' => 'i',
				'sessionKey' => 's',
			)
		),
		self::INSERT => array(
			'type' => 'INSERT',
			'table' => 'adminSessions',
			'tuples' => array(
				'admin_id' => 'i',
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
			'table' => 'adminSessions',
			'set' => array(
				'expiration' => 's',
                                'sessionData' => 's',
			),
			'where' => array(
				'admin_id' => 'i',
				'sessionKey' => 's'
			)
		),
		self::DELETE => array(
			'type' => 'DELETE',
			'table' => 'adminSessions',
			'where' => array(
				'admin_id' => 'i',
				'sessionKey' => 's'
			)
		)
	);

}