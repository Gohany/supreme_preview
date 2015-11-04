<?php

class admin extends controller
{
        /* @var $baseAdminModel baseAdminModel */
        public $baseAdminModel;
	const PRIMARY_MODEL = 'baseAdminModel';

	public $properties = [
		'adminid' => [
			'FILTER' => FILTER_VALIDATE_FLOAT,
			'RETURN_FUNCTION' => [
				'property' => 'baseAdminModel',
				'class' => self::PRIMARY_MODEL,
				'method' => 'fromAdmin_id',
				'includes' => '/environments/base/models/admin.php',
			],
		],
		'email' => [
			'FILTER' => FILTER_SANITIZE_STRING,
			'RETURN_FUNCTION' => [
				'property' => 'baseAdminModel',
				'class' => self::PRIMARY_MODEL,
				'method' => 'fromEmail',
				'includes' => '/environments/base/models/admin.php',
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
	];
        
        public function display()
	{
		if (!is_object($this->baseAdminModel))
		{
			throw new error(errorCodes::ERROR_ACCOUNT_NOT_FOUND);
		}
		$this->runOptions(static::PRIMARY_MODEL);
		$this->output = $this->baseAdminModel;
	}
        
        public function modify()
	{
                
		if (empty($this->baseAdminModel) || empty($this->baseAdminModel->email) || empty($this->input['action']))
		{
			throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
		}
		switch ($this->input['action'])
		{
                        case 'adminSetPermission':
                                
                                require_once $_SERVER['_HTDOCS_'] . '/environments/base/models/adminPermission.php';
                                
                                foreach ($this->input as $name => $value)
                                {
                                        $this->input[strtolower(substr($name, 10))] = $value;
                                }
                                
                                $validPermission = false;
                                foreach ($this->input as $input => $value)
                                {
                                        if (in_array($input, baseAdminPermissionModel::$permissions))
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
                                
                                if ($this->baseAdminModel->permission($permissions))
                                {
                                        error::addError('Admin already has this permission');
                                        throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
                                }
                                
                                $this->baseAdminModel->permissions()->add($permissions);
                                
                                break;
                                
                        case 'adminRemovePermission':
                                
                                require_once $_SERVER['_HTDOCS_'] . '/environments/base/models/adminPermission.php';
                                
                                foreach ($this->input as $name => $value)
                                {
                                        $this->input[strtolower(substr($name, 10))] = $value;
                                }
                                
                                $validPermission = false;
                                foreach ($this->input as $input => $value)
                                {
                                        if (in_array($input, baseAdminPermissionModel::$permissions))
                                        {
                                                $validPermission = true;
                                        }
                                }
                                
                                if (!$validPermission)
                                {
                                        throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
                                }
                                
                                $permissionString = array();
                                $permissionString['environment'] = empty($this->input['environment']) ? null : $this->input['environment'];
                                $permissionString['controller'] = empty($this->input['controller']) ? null : $this->input['controller'];
                                $permissionString['action'] = empty($this->input['action']) ? null : $this->input['action'];
                                $permissionString['modifyAction'] = empty($this->input['modifyAction']) ? null : $this->input['modifyAction'];
                
                                if (!$this->baseAdminModel->permission($permissionString))
                                {
                                        error::addError('Admin does not have this permission');
                                        throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
                                }
                                
                                $this->baseAdminModel->permission($permissionString)->delete();
                                
                                break;
                                
			case 'setEmail':
				
				if (!isset($this->input['email']) || !valid::email($this->input['email']))
				{
					throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
				}

				$this->baseAdminModel->changeEmail($this->input['email']);
				break;
			
			case 'setPassword':
				//Authenticate request
				if (empty($this->input['proof']) || !valid::hex($this->input['proof']))
				{
					error::addError('Invalid proof.');
					throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
				}

				if (!isset($this->resourceLocation[3]))
				{
					error::addError("Resource must be specified.");
					throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
				}
				$srpType = strtolower($this->resourceLocation[3]);

				$this->baseAdminModel->srpfromReply($srpType);
				if ($this->input['proof'] != $this->baseAdminModel->srp->clientProof)
				{
					throw new error(errorCodes::ERROR_PASSWORD_INCORRECT);
				}
                                
				//Do Request
				if (!isset($this->input['newPassword']))
				{
					throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
				}

				$this->baseAdminModel->changePassword($this->input['newPassword']);
				break;
			case 'adminSetEmail':
				if (!isset($this->input['newEmail']) || !valid::email($this->input['newEmail']))
				{
					throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
				}

				$this->baseAdminModel->changeEmail($this->input['newEmail']);
				break;
			case 'adminSetPassword':
				if (!isset($this->input['newPassword']))
				{
					throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
				}
				$this->baseAdminModel->changePassword($this->input['newPassword']);
				break;
			default:
				throw new error(errorCodes::ERROR_ACTION_NOT_FOUND);
		}

		$this->output = $this->baseAdminModel;
	}
        
        public function create()
	{
		$this->createValidation($this->input);
                
		require_once $_SERVER['_HTDOCS_'] . '/environments/base/models/admin.php';

                $this->{self::PRIMARY_MODEL} = baseAdminModel::create($this->input);

		//Set as authed client
		{
			$clientInfo = new clientInfo(clientInfo::CLIENTTYPE_ADMIN, $this->{self::PRIMARY_MODEL}->admin_id, dispatcher::ENVIRONMENT_BASE);
			$clientInfo->setAuthed();
			dataStore::setObject('clientInfo', $clientInfo);
		}

		$this->output =  $this->{self::PRIMARY_MODEL};
	}
        
}