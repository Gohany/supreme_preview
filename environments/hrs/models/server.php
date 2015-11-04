<?php

class hrsServerModel extends baseIdentity
{
        
        ##
	# Constants
	##
	//SRP id type
	const SRP_ID_EMAIL = 'email';
	const SRP_ID_INDEX = 'server_id';
	const SRP_ID_UID = 'uid';
	//SRP password type
	const SRP_PASSWORD_TYPE_PASSWORD = 'password';
        ##
        const OWNER_ID = 'user_id';
        const PASSWORD_DATA_CLASS = 'hrsPasswords';
        const BASE_DB = 'hrs';
        const OWNER_KEY = 'user_id';
        const PRIMARY_ID = 'server_id';
        
        ##
	# Properties
	##
	public $server_id;
        public $user_id;
        public $uid;
	public $email;
	public $firstName;
	public $lastName;
        public $slaves;
	public $date_created;

	##
	# Instances of Models
	##
	public $srp;
	public $info;
	public $sessions;
	public $password;
	public $passwordObject;
        public $status;
        
	##
	# Cache
	##
	const CACHE_EXPIRATION = 3600;
        
	public $doNotCache = array();
	public static $CACHE_KEY = array('server_id', 'database');
	public static $cacheOwner = array(
		'type' => 'user',
		'property' => 'user_id',
	);
        
        public function __construct(array $data = array())
	{
                $this->uid = utility::uidFromPair($data['database'], $data['server_id']);
		parent::__construct($data);
		if ($this->fromCache)
		{
                        $this->setDBs();
			return;
		}
                
                if (!$read = $this->read('hrsServers', array('data' => array('server_id' => $this->server_id))))
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
                        return new hrsServerModel(array('server_id' => $data['id'], 'database' => $data['database']));
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
                $return['server_id'] = $pair['id'];
                return new hrsServerModel($return);
        }
        
        public function selfOrPermission($uid, $X_signature, $authToken, $environment, $controller, $action, $modifyAction)
        {
                if ($this->uid == $uid)
                {
                        return true;
                }
                return $this->serverPermission($X_signature, $authToken, $environment, $controller, $action, $modifyAction);
        }
        
        public function serverPermission($X_signature, $authToken, $environment, $controller, $action, $modifyAction)
        {
                
                // GOD PERMISSION
                if ($this->permission(array('environment' => 'hrs', 'controller' => 'server', 'action' => 'modify', 'modifyAction' => 'setServerPermission')))
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
                return $this->subModel('hrsServerPermissionsModel', 'permissions');
        }
        
        public function permission($permission)
        {
                return $this->permissions()->permissionByArray($permission);
        }
        
        public function sessions($slave_id = 1)
	{
                return $this->subModel('hrsServerSessionsModel', 'sessions', array('slave_id' => $slave_id));
	}
        
        public function status()
        {
                return $this->subModel('hrsServerStatusModel', 'status');
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
                
		if (!$this->write('hrsServers', array(
				'action' => 'update',
				'data' => array(
					'server_id' => $this->server_id,
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

		$hrsServerModel = new hrsServerModel($data);
                if ($hrsServerModel->emailInUse($email))
                {
                        $hrsServerModel->skipDestruct = true;
                        error::addError($email . ' already in use.');
			throw new error(errorCodes::ERROR_EMAIL_IN_USE);
                }
                
                if (!$server_id = $hrsServerModel->write('hrsServers', array(
				'action' => 'insert',
				'data' => $data
			))
		)
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}
                
                $hrsServerModel->server_id = $server_id;
                $data['server_id'] = $server_id;
                
                if (!dataEngine::write('stringTableEmail', array(
                                'database' => 'stringTables', 
                                'dbkey' => self::BASE_KEY, 
				'action' => 'insert',
				'data' => array(
                                    'email' => $hrsServerModel->email,
                                    'environment' => 'hrs',
                                    'model' => 'hrsServerModel',
                                    'database' => $hrsServerModel->database,
                                    'id' => $hrsServerModel->server_id,
                                    'uid' => utility::uidFromPair($hrsServerModel->database, $hrsServerModel->server_id),
                                )
			))
		)
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		if (
			$data['password'] !== null
			&& !$hrsServerModel->write(self::PASSWORD_DATA_CLASS, array(
				'action' => 'insert',
				'data' => array(
					'passwordHash' => $hrsServerModel->password(true)->passwordHash(),
					'passwordIndex' => $hrsServerModel->password(true)->index(),
					'salt' => $hrsServerModel->password(true)->salt
				)
			))
		)
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		return $hrsServerModel;
	}
        
        public function emailInUse($email)
	{
		return (bool) $this->read('hrsServers', array('data' => array('email' => strtolower($email))));
	}
        
}