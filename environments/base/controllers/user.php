<?php

class user extends identityController
{
        
        public $baseUserModel;
	const PRIMARY_MODEL = 'baseUserModel';

	public $properties = [
		'userid' => [
			'FILTER' => FILTER_VALIDATE_FLOAT,
			'RETURN_FUNCTION' => [
				'property' => 'baseUserModel',
				'class' => self::PRIMARY_MODEL,
				'method' => 'fromUser_id',
				'includes' => '/environments/base/models/user.php',
			],
		],
		'email' => [
			'FILTER' => FILTER_SANITIZE_STRING,
			'RETURN_FUNCTION' => [
				'property' => 'baseUserModel',
				'class' => self::PRIMARY_MODEL,
				'method' => 'fromEmail',
				'includes' => '/environments/base/models/user.php',
			],
		],
	];
        
        public $create_data = [
		'password' => [
			'RETURN_FUNCTION' => [
				'class' => 'valid',
				'method' => 'password'
			],
			'ERROR_CODE' => errorCodes::ERROR_PASSWORD_BAD
		],
		'email' => [
			'RETURN_FUNCTION' => [
				'class' => 'valid',
				'method' => 'email'
			],
			'ERROR_CODE' => errorCodes::ERROR_EMAIL_INVALID
		],
                'firstName' => [
			'RETURN_FUNCTION' => [
				'class' => 'valid',
				'method' => 'realName'
			],
			'optional' => true
		],
                'lastName' => [
			'RETURN_FUNCTION' => [
				'class' => 'valid',
				'method' => 'realName'
			],
			'optional' => true
		],
	];

        public function modify()
	{
                
		if (empty($this->{static::PRIMARY_MODEL}) || empty($this->input['action']))
		{
			throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
		}
		switch ($this->input['action'])
		{
			case 'setEmail':
			case 'setPassword':
			case 'adminSetEmail':
			case 'adminSetPassword':
                                parent::modify();
                                break;
			default:
				throw new error(errorCodes::ERROR_ACTION_NOT_FOUND);
		}

		$this->output = $this->{static::PRIMARY_MODEL};
	}
        
}