<?php

class baseAdminModel extends model
{
        
        ##
	# Constants
	##
	//SRP id type
	const SRP_ID_EMAIL = 'email';
	const SRP_ID_ADMINID = 'adminid';
	//SRP password type
	const SRP_PASSWORD_TYPE_PASSWORD = 'password';
        ##
        const OWNER_ID = 'admin_id';
        const BASE_DB = 'base';
        const DATABASE_DEFAULT = 1;
        
        ##
	# Properties
	##
	public $admin_id;
	public $email;
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
        public $permissions;
	##
	# Cache
	##
	const CACHE_EXPIRATION = 3600;
        const BASE_MODEL = true;
        
	public $doNotCache = array();
	public static $CACHE_KEY = array('admin_id');
	public static $cacheOwner = array(
		'type' => 'admin',
		'property' => 'admin_id',
	);
        
        public function __construct(array $data = array())
	{
		parent::__construct($data);
		if ($this->fromCache)
		{
                        $this->setDBs();
			return;
		}
                
                if (!$read = $this->read('admins', array('data' => array('admin_id' => $this->admin_id))))
		{
			throw new error(errorCodes::ERROR_ACCOUNT_NOT_FOUND);
		}
                
		$this->loadProperties($read);
                $this->setDBs();
	}
        
        public static function fromAdmin_id($admin_id)
	{
		return new baseAdminModel(array('admin_id' => $admin_id));
	}
        // TODO
        // Make query level caching for searches!
        public static function fromEmail($email)
	{
		if (!$data = dataEngine::read('admins', array('database' => self::BASE_DB, 'dbkey' => self::BASE_KEY, 'data' => array('email' => $email))))
		{
			throw new error(errorCodes::ERROR_ACCOUNT_NOT_FOUND);
		}
		return new baseAdminModel($data);
	}
        
        public function validateAdmin($X_signature, $authToken, $environment, $controller, $action, $modifyAction = null)
        {
                
                if (!$this->sessions()->isValidIteration($X_signature, $authToken))
                {
                        throw new error(errorCodes::ERROR_INTERNAL_ERROR);
                }
                
                // GOD PERMISSION
                if ($this->permission(array('environment' => 'base', 'controller' => 'admin', 'action' => 'modify', 'modifyAction' => 'adminSetPermission')))
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
                require_once $_SERVER['_HTDOCS_'] . '/environments/base/models/adminPermissions.php';
                $this->permissions || $this->permissions = new baseAdminPermissionsModel(array('admin_id' => $this->admin_id, 'database' => $this->database));
                return $this->permissions;
        }
        
        public function permission($permission)
        {
                return $this->permissions()->permissionByArray($permission);
        }
        
        public function srpfromPreAuth($A, $idType = self::SRP_ID_EMAIL, $passwordType = self::SRP_PASSWORD_TYPE_PASSWORD)
	{
		switch ($idType)
		{
			case self::SRP_ID_EMAIL:
				$id = $this->email;
				break;
			case self::SRP_ID_ADMINID:
				$id = $this->admin_id;
				break;
			default:
				throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		require_once $_SERVER['_HTDOCS_'] . '/base_classes/utility/srp.php';

		switch ($passwordType)
		{
			case self::SRP_PASSWORD_TYPE_PASSWORD:
				$password = $this->password()->passwordHash;
				$salt = $this->password()->salt;
				break;
			default:
				error::addError('Invalid Password type.');
				throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
		}
		
		$this->srp = srpModel::fromPreAuth($id, $A, $password, $salt);

		$cacheEntry = new cacheEntry('SRP_admin_' . $id, serialize($this->srp), srpModel::CACHE_LOGIN_RESPONSE_TIME);
		$cacheEntry->lock();
		if (!$cacheEntry->setIfNotExist())
		{
			$cacheEntry->unlock();
			throw new error(errorCodes::ERROR_LOGIN_IN_PROCESS);
		}
		$cacheEntry->unlock();
		return $this->srp;
	}

	public function srpfromReply($idType = self::SRP_ID_EMAIL)
	{
		switch ($idType)
		{
			case self::SRP_ID_EMAIL:
				$id = $this->email;
				break;
			case self::SRP_ID_ADMINID:
				$id = $this->admin_id;
				break;
			default:
				throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		require_once $_SERVER['_HTDOCS_'] . '/base_classes/utility/srp.php';
                
		$cacheEntry = new cacheEntry('SRP_admin_' . $id);
		$cacheEntry->lock();
		if (!$cacheEntry->get())
		{
			$cacheEntry->unlock();
			throw new error(errorCodes::ERROR_REQUEST_TIMED_OUT);
		}
		$this->srp = srpModel::fromReply($id, get_object_vars(unserialize($cacheEntry->value)));
		$cacheEntry->delete();
		$cacheEntry->unlock();
		return $this->srp;
	}
        
        /* @var $sessions baseAdminSessionsModel */
        public function sessions()
	{
		require_once $_SERVER['_HTDOCS_'] . '/environments/base/models/adminSessions.php';
		$this->sessions || $this->sessions = new baseAdminSessionsModel(array('admin_id' => $this->admin_id, 'database' => $this->database));
		return $this->sessions;
	}
        
        public function password($new = false)
	{
		require_once $_SERVER['_HTDOCS_'] . '/base_classes/utility/password.php';

		if ($this->password !== null)
		{
			$this->passwordObject = new passwordModel(array('password' => $this->password, 'user_id' => $this->admin_id));
			$this->password = null;
			return $this->passwordObject;
		}

		if ($new == false && $this->passwordObject !== null)
		{
			return $this->passwordObject;
		}

		$password = array('passwordIndex' => passwordModel::generateIndex($this->admin_id));
		if (!($data = $this->read('adminPasswords', array(
				'data' => array(
					'passwordIndex' => $password['passwordIndex']
				)
			))
			) && $new == false
		)
		{
			error::addError('Missing password information');
			throw new error(errorCodes::ERROR_NO_PASSWORD);
		}
		$password['passwordHash'] = $data['passwordHash'];
		$password['salt'] = $data['salt'];
		$password['admin_id'] = $this->admin_id;

		$this->passwordObject || $this->passwordObject = new passwordModel($password);
		return $this->passwordObject;
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
                
		if (!$this->write('admins', array(
				'action' => 'update',
				'data' => array(
					'admin_id' => $this->admin_id,
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
        
        public function changePassword($password)
	{
		if (!valid::password($password))
		{
			throw new error(errorCodes::ERROR_PASSWORD_BAD);
		}
		$this->password = $password;
		$this->password(true);
		if (!$this->write('adminPasswords', array(
				'action' => 'update',
				'data' => array(
					'passwordIndex' => $this->passwordObject->index(),
					'passwordHash' => $this->passwordObject->passwordHash(),
					'salt' => $this->passwordObject->salt
				)
			))
		)
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		return true;
	}
        
        public static function create($input)
	{
		
		$email = strtolower($input['email']);
		if (self::isEmailInUse($email))
		{
			error::addError($email . ' already in use.');
			throw new error(errorCodes::ERROR_EMAIL_IN_USE);
		}

		$data = [
			'email' => $email,
                        'fromCache' => true,
                        'database' => self::DATABASE_DEFAULT,
		];
                
		if ($input['password'] !== null)
		{
			$data['password'] = $input['password'];
		}

		$baseAdminModel = new baseAdminModel($data);
                if (!$admin_id = $baseAdminModel->write('admins', array(
				'action' => 'insert',
				'data' => $data
			))
		)
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}
                $baseAdminModel->admin_id = $admin_id;
                $data['admin_id'] = $admin_id;

		if (
			$data['password'] !== null
			&& !$baseAdminModel->write('adminPasswords', array(
				'action' => 'insert',
				'data' => array(
					'passwordHash' => $baseAdminModel->password(true)->passwordHash(),
					'passwordIndex' => $baseAdminModel->password(true)->index(),
					'salt' => $baseAdminModel->password(true)->salt
				)
			))
		)
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		return $baseAdminModel;
	}
        
        public static function isEmailInUse($email)
	{
		return (bool) dataEngine::read('admins', array('database' => self::BASE_DB, 'dbkey' => self::BASE_KEY, 'data' => array('email' => strtolower($email))));
	}
        
}