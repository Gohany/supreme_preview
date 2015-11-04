<?php

class email extends controller
{
        
        public $broadcastEmailModel;
        public $baseUserModel;
	const PRIMARY_MODEL = 'broadcastEmailModel';

	public $properties = [
		'emailid' => [
			'FILTER' => FILTER_VALIDATE_FLOAT,
			'RETURN_FUNCTION' => [
				'property' => self::PRIMARY_MODEL,
				'class' => self::PRIMARY_MODEL,
				'method' => 'fromEmail_id',
				'includes' => '/environments/broadcast/models/email.php',
			],
		],
                'userid' => [
			'FILTER' => FILTER_VALIDATE_FLOAT,
			'RETURN_FUNCTION' => [
				'property' => 'baseUserModel',
				'class' => 'baseUserModel',
				'method' => 'fromUser_id',
				'includes' => '/environments/base/models/user.php',
			],
		],
		'email' => [
			'FILTER' => FILTER_SANITIZE_STRING,
			'RETURN_FUNCTION' => [
				'property' => self::PRIMARY_MODEL,
				'class' => self::PRIMARY_MODEL,
				'method' => 'fromEmail',
				'includes' => '/environments/broadcast/models/email.php',
			],
		],
	];
        
        public $create_data = [
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
        
        public function display()
	{
		if (!is_object($this->broadcastEmailModel))
		{
			throw new error(errorCodes::ERROR_ACCOUNT_NOT_FOUND);
		}
		$this->runOptions(static::PRIMARY_MODEL);
		$this->output = $this->broadcastEmailModel;
	}
        
        public function modify()
	{
                
		if (empty($this->broadcastEmailModel) || empty($this->broadcastEmailModel->email) || empty($this->input['action']))
		{
			throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
		}
		switch ($this->input['action'])
		{
			case 'setNames':
				$this->broadcastEmailModel->setNames($this->input['firstName'], $this->input['lastName']);
				break;
			case 'addGroup':
				$this->broadcastEmailModel->addGroup($this->input['group']);
				break;
                        case 'removeGroup':
                                $this->broadcastEmailModel->removeGroup($this->input['group']);
                                break;
			default:
				throw new error(errorCodes::ERROR_ACTION_NOT_FOUND);
		}

		$this->output = $this->broadcastEmailModel;
	}
        
        public function create()
	{
		$this->createValidation($this->input);
                
		require_once $_SERVER['_HTDOCS_'] . '/environments/base/models/user.php';
                $this->{self::PRIMARY_MODEL} = broadcastEmailModel::create($this->baseUserModel, $this->input);
		$this->output =  $this->{self::PRIMARY_MODEL};
	}
        
}