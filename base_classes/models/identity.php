<?php

class baseIdentity extends model
{
        
        ##
	# Constants
	##
	//SRP id type
	const SRP_ID_EMAIL = 'email';
	const SRP_ID_INDEX = 'index';
	//SRP password type
	const SRP_PASSWORD_TYPE_PASSWORD = 'password';
        
        public function srpfromPreAuth($A, $idType = self::SRP_ID_EMAIL, $passwordType = self::SRP_PASSWORD_TYPE_PASSWORD)
	{
		switch ($idType)
		{
			case static::SRP_ID_EMAIL:
				$id = $this->email;
				break;
                        case static::SRP_ID_UID:
                                $id = $this->uid;
                                break;
			case static::SRP_ID_INDEX:
				$id = $this->{static::PRIMARY_ID};
				break;
			default:
				throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		require_once $_SERVER['_HTDOCS_'] . '/base_classes/utility/srp.php';

		switch ($passwordType)
		{
			case static::SRP_PASSWORD_TYPE_PASSWORD:
				$password = $this->password()->passwordHash;
				$salt = $this->password()->salt;
				break;
			default:
				error::addError('Invalid Password type.');
				throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
		}
		
		$this->srp = srpModel::fromPreAuth($id, $A, $password, $salt);

		$cacheEntry = new cacheEntry('SRP_account_' . $id, serialize($this->srp), srpModel::CACHE_LOGIN_RESPONSE_TIME);
		$cacheEntry->lock();
		if (!$cacheEntry->setIfNotExist())
		{
			$cacheEntry->unlock();
			throw new error(errorCodes::ERROR_LOGIN_IN_PROCESS);
		}
		$cacheEntry->unlock();
		return $this->srp;
	}
        
        public function changePassword($password)
	{
		if (!valid::password($password))
		{
			throw new error(errorCodes::ERROR_PASSWORD_BAD);
		}
		$this->password = $password;
		$this->password(true);
		if (!$this->write(static::PASSWORD_DATA_CLASS, array(
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

	public function srpfromReply($idType = self::SRP_ID_EMAIL)
	{
		switch ($idType)
		{
			case static::SRP_ID_EMAIL:
				$id = $this->email;
				break;
                        case static::SRP_ID_UID:
                                $id = $this->uid;
                                break;
			case static::SRP_ID_INDEX:
                                $id = $this->{static::PRIMARY_ID};
				break;
			default:
				throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		require_once $_SERVER['_HTDOCS_'] . '/base_classes/utility/srp.php';
                
		$cacheEntry = new cacheEntry('SRP_account_' . $id);
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
        
        public function password($new = false)
	{
		require_once $_SERVER['_HTDOCS_'] . '/base_classes/utility/password.php';

		if ($this->password !== null)
		{
			$this->passwordObject = new passwordModel(array('password' => $this->password, 'index' => $this->{static::PRIMARY_ID}));
			$this->password = null;
			return $this->passwordObject;
		}

		if ($new == false && $this->passwordObject !== null)
		{
			return $this->passwordObject;
		}

		$password = array('passwordIndex' => passwordModel::generateIndex($this->{static::PRIMARY_ID}));
		if (!($data = $this->read(static::PASSWORD_DATA_CLASS, array(
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
                $password['index'] = $this->{static::PRIMARY_ID};

		$this->passwordObject || $this->passwordObject = new passwordModel($password);
		return $this->passwordObject;
	}
        
}