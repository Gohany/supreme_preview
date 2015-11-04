<?PHP

class session extends controller
{
	public $baseUserModel;

	const PRIMARY_MODEL = 'baseUserModel';

	public $properties = array(
		'accountid' => array(
			'FILTER' => FILTER_VALIDATE_FLOAT,
			'RETURN_FUNCTION' => array(
				'property' => 'baseUserModel',
				'class' => self::PRIMARY_MODEL,
				'method' => 'fromUser_id',
				'includes' => '/environments/base/models/user.php'
			)
		),
		'email' => array(
			'FILTER' => FILTER_SANITIZE_STRING,
			'RETURN_FUNCTION' => array(
				'property' => 'baseUserModel',
				'class' => self::PRIMARY_MODEL,
				'method' => 'fromEmail',
				'includes' => '/environments/base/models/user.php'
			)
		)
	);
	public $create_data = array(
		'id' => array(
			'RETURN_FUNCTION' => array(
				'class' => 'valid',
				'method' => 'email'
			),
			'ERROR_CODE' => errorCodes::ERROR_INVALID_ID
		),
		'proof' => array(
			'RETURN_FUNCTION' => array(
				'class' => 'valid',
				'method' => 'hex'
			),
			'ERROR_CODE' => errorCodes::ERROR_PASSWORD_BAD
		)
	);

	public function display()
	{
		if (empty($this->baseUserModel->email))
		{
			throw new error(errorCodes::ERROR_RESOURCE_NOT_FOUND);
		}

		if (!$this->baseUserModel->sessions())
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		if (empty($this->baseUserModel->sessions()->sessions))
		{
			error::addError('This user has no session.');
			throw new error(errorCodes::ERROR_SESSION_NOT_FOUND);
		}

		$this->output = $this->baseUserModel->sessions;
	}

	public function create()
	{
		require_once $_SERVER['_HTDOCS_'] . '/base_classes/utility/srp.php';
		require_once $_SERVER['_HTDOCS_'] . '/environments/base/models/sessions.php';

		//Validate input
		{
			if (empty($this->baseUserModel->email))
			{
				throw new error(errorCodes::ERROR_RESOURCE_NOT_FOUND);
			}

			if (empty($this->input['proof']))
			{
				throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
			}

			$create_data = array_merge(array('id' => $this->baseUserModel->email), $this->input);
			$this->createValidation($create_data);
		}

		//Set session type
		if (isset($this->input['type']) && !in_array($this->input['type'], array_keys(baseSessionsModel::$sessionsPerType)))
		{
			throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
		}
		elseif (!isset($this->input['type']))
		{
			$this->input['type'] = baseSessionsModel::SESSION_TYPE_WEBSITE;
		}

		$resourceType = strtolower($this->resourceLocation[3]);

		//Check if password is correct!
		if ($this->input['proof'] !== $this->baseUserModel->srpfromReply($resourceType)->clientProof)
		{
			error::addError('Proof mismatch');
			throw new error(errorCodes::ERROR_PASSWORD_INCORRECT);
		}
                
		//Create session
		$this->baseUserModel->sessions()->add($this->baseUserModel->srp->serverSessionKey, $this->input['type']);

		$login = array(
			'ipLong' => ip2long(geoip::getClientIP()),
			'loginType' => $this->input['type'],
			'user_id' => $this->baseUserModel->user_id,
                        'sessionKey' => $this->baseUserModel->srp->serverSessionKey,
                        'type' => $this->input['type'],
                        'geoip' => region::getClientRegion(),
		);
                
		if (!$this->baseUserModel->sessions()->write('logins', array(
				'action' => 'insert',
				'data' => $login,
			)))
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		$clientInfo = new clientInfo(clientInfo::CLIENTTYPE_CLIENT, $this->baseUserModel->user_id, 'base', $this->baseUserModel->srp->serverSessionKey);
		$clientInfo->setAuthed();

		dataStore::setObject('clientInfo', $clientInfo);
		$this->runOptions(static::PRIMARY_MODEL);
		$clientInfo = dataStore::getObject('clientInfo');
		$this->output = $this->baseUserModel;

	}

	public function delete()
	{
		$clientInfo = dataStore::getObject('clientInfo');
		if (!$clientInfo)
		{
			return;
		}

		if ($clientInfo->isClient())
		{
			$this->baseUserModel->clientSessions()->removeByKey($clientInfo->getSignature());
		}
		else
		{
			$this->baseUserModel->clientSessions()->removeAll();
		}
	}
}