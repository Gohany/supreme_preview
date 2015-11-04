<?php 

class stringTableEmail_dataClass extends dataClassMysql
{

	public static $queries = array(
		self::SELECT => array(
			'type' => 'SELECT',
			'table' => 'email',
			'where' => array(
                                'email' => 'e',
                                'environment' => 's',
                                'model' => 's',
			),
			'limit_multiplier' => 1
		),
		self::INSERT => array(
			'type' => 'INSERT',
			'table' => 'email',
			'tuples' => array(
                                'email' => 'e',
                                'environment' => 's',
                                'model' => 's',
                                'database' => 'i',
                                'id' => 'i',
                                'uid' => 'i',
			)
		),
		self::UPDATE => array(
			'type' => 'UPDATE',
			'table' => 'email',
			'set' => array(
				'email' => 'e',
                                'environment' => 's',
                                'model' => 's',
                                'database' => 'i',
                                'id' => 'i',
                                'uid' => 'i',
			),
			'where' => array(
				'email' => 'e',
                                'environment' => 's',
                                'model' => 's',
			),
			'limit_multiplier' => 1
		),
		self::DELETE => array(
			'type' => 'DELETE',
			'table' => 'email',
			'where' => array(
				'email' => 'e',
                                'environment' => 's',
                                'model' => 's',
			),
			'limit_multiplier' => 1
		)
	);

}