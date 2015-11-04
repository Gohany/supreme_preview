<?php

class hrsAccountModel extends baseIdentity
{
        
        ##
	# Constants
	##
	//SRP id type
	const SRP_ID_EMAIL = 'email';
	const SRP_ID_INDEX = 'account_id';
	const SRP_ID_UID = 'uid';
	//SRP password type
	const SRP_PASSWORD_TYPE_PASSWORD = 'password';
        ##
        const OWNER_ID = 'user_id';
        const PASSWORD_DATA_CLASS = 'hrsPasswords';
        const BASE_DB = 'hrs';
        const OWNER_KEY = 'user_id';
        const PRIMARY_ID = 'account_id';
        
        ##
	# Properties
	##
	public $account_id;
        public $user_id;
        public $uid;
	public $email;
	public $firstName;
	public $lastName;
	public $date_created;

	##
	# Instances of Models
	##
	public $srp;
	public $info;
	public $sessions;
	public $password;
	public $passwordObject;

	##
	# Cache
	##
	const CACHE_EXPIRATION = 3600;
        
	public $doNotCache = array();
	public static $CACHE_KEY = array('account_id', 'database');
	public static $cacheOwner = array(
		'type' => 'user',
		'property' => 'user_id',
	);
        
        public function __construct(array $data = array())
	{
                $this->uid = utility::uidFromPair($data['database'], $data['account_id']);
		parent::__construct($data);
		if ($this->fromCache)
		{
                        $this->setDBs();
			return;
		}
                
                if (!$read = $this->read('hrsAccounts', array('data' => array('account_id' => $this->account_id))))
                {
                        throw new error(errorCodes::ERROR_ACCOUNT_NOT_FOUND);
                }
                
		$this->loadProperties($read);
                $this->setDBs();
	}
        
        public static function fromData($data)
        {
                if (!empty($data['id']) && !empty($data['database']))
                {
                        return new hrsAccountModel(array('account_id' => $data['id'], 'database' => $data['database']));
                }
                elseif (!empty($data['uid']))
                {
                        return self::fromUid($data['uid']);
                }
                throw new error(errorCodes::ERROR_ACCOUNT_NOT_FOUND);
        }

        public static function fromUid($uid)
        {
                $pair = utility::pairFromUid($uid);
                $return['database'] = $pair['database'];
                $return['account_id'] = $pair['id'];
                return new hrsAccountModel($return);
        }
        
        public function selfOrPermission($uid, $X_signature, $authToken, $environment, $controller, $action, $modifyAction)
        {
                if ($this->uid == $uid)
                {
                        return true;
                }
                return $this->accountPermission($X_signature, $authToken, $environment, $controller, $action, $modifyAction);
        }
        
        public function accountPermission($X_signature, $authToken, $environment, $controller, $action, $modifyAction)
        {
                
                // GOD PERMISSION
                if ($this->permission(array('environment' => 'hrs', 'controller' => 'account', 'action' => 'modify', 'modifyAction' => 'setAccountPermission')))
                {
                        return true;
                }
                
                $permissionSet = array('environment' => $environment, 'controller' => $controller, 'action' => $action, 'modifyAction' => $modifyAction);
                if ($this->permission($permissionSet))
                {
                        return true;
                }
                return false;
        }
        
        public function permissions()
        {
                return $this->subModel('hrsAccountPermissionsModel', 'permissions');
        }
        
        public function permission($permission)
        {
                return $this->permissions()->permissionByArray($permission);
        }
        
        public function sessions()
	{
                return $this->subModel('hrsSessionsModel', 'sessions');
	}
        
        public function changeEmail($newEmail)
	{
                
		$newEmail = strtolower($newEmail);
		// do a read on all unique strings first
		if ($this->emailInUse($newEmail))
		{
			error::addError($newEmail . ' already exists');
			throw new error(errorCodes::ERROR_EMAIL_IN_USE);
		}
                
		if (!$this->write('hrsAccounts', array(
				'action' => 'update',
				'data' => array(
					'account_id' => $this->account_id,
					'email' => $newEmail
				)
			))
		)
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		$this->email = $newEmail;
		return true;
	}
        
        public static function create($input, $ownerObject = null)
	{
		
                
		$email = strtolower($input['email']);
                
		$data = [
                        'user_id' => $ownerObject->user_id,
			'email' => $email,
			'firstName' => $input['firstName'],
                        'lastName' => $input['lastName'],
                        'fromCache' => true,
                        'database' => $ownerObject->database,
		];
                
		if ($input['password'] !== null)
		{
			$data['password'] = $input['password'];
		}

		$hrsAccountModel = new hrsAccountModel($data);
                if ($hrsAccountModel->emailInUse($email))
                {
                        $hrsAccountModel->skipDestruct = true;
                        error::addError($email . ' already in use.');
			throw new error(errorCodes::ERROR_EMAIL_IN_USE);
                }
                
                if (!$account_id = $hrsAccountModel->write('hrsAccounts', array(
				'action' => 'insert',
				'data' => $data
			))
		)
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}
                
                $hrsAccountModel->account_id = $account_id;
                $data['account_id'] = $account_id;
                
                if (!dataEngine::write('stringTableEmail', array(
                                'database' => 'stringTables', 
                                'dbkey' => self::BASE_KEY, 
				'action' => 'insert',
				'data' => array(
                                    'email' => $hrsAccountModel->email,
                                    'environment' => 'hrs',
                                    'model' => 'hrsAccountModel',
                                    'database' => $hrsAccountModel->database,
                                    'id' => $hrsAccountModel->account_id,
                                    'uid' => utility::uidFromPair($hrsAccountModel->database, $hrsAccountModel->account_id),
                                )
			))
		)
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		if (
			$data['password'] !== null
			&& !$hrsAccountModel->write(self::PASSWORD_DATA_CLASS, array(
				'action' => 'insert',
				'data' => array(
					'passwordHash' => $hrsAccountModel->password(true)->passwordHash(),
					'passwordIndex' => $hrsAccountModel->password(true)->index(),
					'salt' => $hrsAccountModel->password(true)->salt
				)
			))
		)
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		return $hrsAccountModel;
	}
        
        public function emailInUse($email)
	{
		return (bool) $this->read('hrsAccounts', array('data' => array('email' => strtolower($email))));
	}
        
}