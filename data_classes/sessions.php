<?php
class sessions_dataClass extends dataClassMysql
{

	public static $serializedFields = array('clientData', 'sessionData');
	public static $queries = array(
		self::SELECT => array(
			'type' => 'SELECT',
			'table' => 'sessions',
			'where' => array(
				'user_id' => 'i',
				'sessionKey' => 's',
			)
		),
		self::INSERT => array(
			'type' => 'INSERT',
			'table' => 'sessions',
			'tuples' => array(
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
			'table' => 'sessions',
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
			'table' => 'sessions',
			'where' => array(
				'user_id' => 'i',
				'sessionKey' => 's'
			)
		)
	);

}