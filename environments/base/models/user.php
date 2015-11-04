<?php

class baseUserModel extends baseIdentity
{
        
        ##
	# Constants
	##
	//SRP id type
	const SRP_ID_EMAIL = 'email';
	const SRP_ID_INDEX = 'userid';
	//SRP password type
	const SRP_PASSWORD_TYPE_PASSWORD = 'password';
        ##
        const PRIMARY_ID = 'user_id';
        const OWNER_ID = 'user_id';
        const BASE_DB = 'base';
        const DATABASE_DEFAULT = 1;
        const DEFAULT_INDEX = 'user_id';
        const PASSWORD_DATA_CLASS = 'passwords';
        
        ##
	# Properties
	##
	public $user_id;
	public $email;
	public $firstName;
	public $lastName;
        public $database;
	public $date_created;

	##
	# Instances of Models
	##
	public $srp;
	public $info;
	public $sessions;
	public $password;
	public $passwordObject;
	public $userGroup;
        public $emailLists;
        public $emailList;
        public $domains;
        public $domain;
        public $form;
        public $forms;

	##
	# Cache
	##
	const CACHE_EXPIRATION = 3600;
        const BASE_MODEL = true;
        
	public $doNotCache = array();
	public static $CACHE_KEY = array('user_id');
	public static $cacheOwner = array(
		'type' => 'user',
		'property' => 'user_id',
	);
        
        public function __construct(array $data = array())
	{
		parent::__construct($data);
		if ($this->fromCache)
		{
                        $this->setDBs();
			return;
		}
                
                if (!$read = $this->read('users', array('data' => array('user_id' => $this->user_id))))
		{
			throw new error(errorCodes::ERROR_ACCOUNT_NOT_FOUND);
		}
                
		$this->loadProperties($read);
                $this->setDBs();
	}
        
        public static function fromUser_id($user_id)
	{
		return new baseUserModel(array('user_id' => $user_id));
	}
        // TODO
        // Make query level caching for searches!
        public static function fromEmail($email)
	{
		if (!$data = dataEngine::read('users', array('database' => self::BASE_DB, 'dbkey' => self::BASE_KEY, 'data' => array('email' => $email))))
		{
			throw new error(errorCodes::ERROR_ACCOUNT_NOT_FOUND);
		}
		return new baseUserModel($data);
	}
        
        public function domains()
        {
                return $this->subModel('frontendDomainsModel', 'domains');
        }
        
        public function forms()
        {
                return $this->subModel('frontendFormsModel', 'forms');
        }
        
        public function sessions()
	{
                return $this->subModel('baseSessionsModel', 'sessions');
	}
        
        public function emailLists()
        {
                require_once $_SERVER['_HTDOCS_'] . '/environments/broadcast/models/emailLists.php';
                $this->emailLists || $this->emailLists = new broadcastEmailListsModel(array('user_id' => $this->user_id, 'database' => $this->database));
                return $this->emailLists;
        }
        
        public function emailListFromId($id)
        {
                $this->emailList || $this->emailList = $this->emailLists()->emailListByID($id);
                return $this->emailList;
        }
        
        public function emailListFromName($name)
        {
                $this->emailList || $this->emailList = $this->emailLists()->emailListByName($name);
                return $this->emailList;
        }
        
        public function changeEmail($newEmail)
	{
                
		$newEmail = strtolower($newEmail);
		// do a read on all unique strings first
		if (self::isEmailInUse($newEmail))
		{
			error::addError($newEmail . 'already exists');
			throw new error(errorCodes::ERROR_EMAIL_IN_USE);
		}
                
		if (!$this->write('users', array(
				'action' => 'update',
				'data' => array(
					'user_id' => $this->user_id,
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
		if (self::isEmailInUse($email))
		{
			error::addError($email . ' already in use.');
			throw new error(errorCodes::ERROR_EMAIL_IN_USE);
		}

		$data = [
			'email' => $email,
			'firstName' => $input['firstName'],
                        'lastName' => $input['lastName'],
                        'fromCache' => true,
                        'database' => self::DATABASE_DEFAULT,
		];
                
		if ($input['password'] !== null)
		{
			$data['password'] = $input['password'];
		}

		$baseUserModel = new baseUserModel($data);
                if (!$user_id = $baseUserModel->write('users', array(
				'action' => 'insert',
				'data' => $data
			))
		)
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}
                $baseUserModel->user_id = $user_id;
                $data['user_id'] = $user_id;

		if (
			$data['password'] !== null
			&& !$baseUserModel->write(self::PASSWORD_DATA_CLASS, array(
				'action' => 'insert',
				'data' => array(
					'passwordHash' => $baseUserModel->password(true)->passwordHash(),
					'passwordIndex' => $baseUserModel->password(true)->index(),
					'salt' => $baseUserModel->password(true)->salt
				)
			))
		)
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		return $baseUserModel;
	}
        
        public static function isEmailInUse($email)
	{
		return (bool) dataEngine::read('users', array('database' => self::BASE_DB, 'dbkey' => self::BASE_KEY, 'data' => array('email' => strtolower($email))));
	}
        
}