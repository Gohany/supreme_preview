<?php

class account extends identityController
{
        
        public $hrsAccountModel;
        public $ownerModel;
	const PRIMARY_MODEL = 'hrsAccountModel';

	public $properties = [
                'userid' => [
			'FILTER' => FILTER_VALIDATE_FLOAT,
			'RETURN_FUNCTION' => [
				'property' => 'ownerModel',
				'class' => 'baseUserModel',
				'method' => 'fromUser_id',
				'includes' => '/environments/base/models/user.php',
			],
		],
		'uid' => [
			'FILTER' => FILTER_VALIDATE_FLOAT,
			'RETURN_FUNCTION' => [
				'property' => self::PRIMARY_MODEL,
				'class' => self::PRIMARY_MODEL,
				'method' => 'fromUid',
				'includes' => '/environments/hrs/models/account.php',
			],
		],
		'email' => [
			'FILTER' => FILTER_SANITIZE_STRING,
			'RETURN_FUNCTION' => [
				'property' => self::PRIMARY_MODEL,
				'class' => self::PRIMARY_MODEL,
				'method' => 'fromData',
				'includes' => '/environments/hrs/models/account.php',
                                'stringLookup' => 'email',
			],
		],
                'username' => [
			'FILTER' => FILTER_SANITIZE_STRING,
			'RETURN_FUNCTION' => [
				'property' => self::PRIMARY_MODEL,
				'class' => self::PRIMARY_MODEL,
				'method' => 'fromUsername',
				'includes' => '/environments/hrs/models/account.php',
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
                        case 'setAccountPermission':
                        case 'adminSetAccountPermission':
                                
                                require_once $_SERVER['_HTDOCS_'] . '/environments/hrs/models/accountPermission.php';
                                
                                foreach ($this->input as $name => $value)
                                {
                                        $this->input[strtolower(substr($name, 10))] = $value;
                                }
                                
                                $validPermission = false;
                                foreach ($this->input as $input => $value)
                                {
                                        if (in_array($input, hrsAccountPermissionModel::$permissions))
                                        {
                                                $validPermission = true;
                                        }
                                }
                                
                                if (!$validPermission)
                                {
                                        throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
                                }
                                
                                $permissions = array();
                                $permissions['environment'] = empty($this->input['environment']) ? null : $this->input['environment'];
                                $permissions['controller'] = empty($this->input['controller']) ? null : $this->input['controller'];
                                $permissions['action'] = empty($this->input['action']) ? null : $this->input['action'];
                                $permissions['modifyAction'] = empty($this->input['modifyaction']) ? null : $this->input['modifyaction'];
                                
                                if ($this->{static::PRIMARY_MODEL}->permission($permissions))
                                {
                                        error::addError('Account already has this permission');
                                        throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
                                }
                                
                                $this->{static::PRIMARY_MODEL}->permissions()->add($permissions);
                                break;
                                
			default:
				throw new error(errorCodes::ERROR_ACTION_NOT_FOUND);
		}

		$this->output = $this->{static::PRIMARY_MODEL};
	}
        
}