<?PHP

class adminSession extends controller
{
        /* @var $baseAdminModel baseAdminModel */
        
	public $baseAdminModel;

	const PRIMARY_MODEL = 'baseAdminModel';

	public $properties = array(
		'accountid' => array(
			'FILTER' => FILTER_VALIDATE_FLOAT,
			'RETURN_FUNCTION' => array(
				'property' => 'baseAdminModel',
				'class' => self::PRIMARY_MODEL,
				'method' => 'fromAdmin_id',
				'includes' => '/environments/base/models/admin.php'
			)
		),
		'email' => array(
			'FILTER' => FILTER_SANITIZE_STRING,
			'RETURN_FUNCTION' => array(
				'property' => 'baseAdminModel',
				'class' => self::PRIMARY_MODEL,
				'method' => 'fromEmail',
				'includes' => '/environments/base/models/admin.php'
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
		if (empty($this->baseAdminModel->email))
		{
			throw new error(errorCodes::ERROR_RESOURCE_NOT_FOUND);
		}

		if (!$this->baseAdminModel->sessions())
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		if (empty($this->baseAdminModel->sessions()->sessions))
		{
			error::addError('This admin has no session.');
			throw new error(errorCodes::ERROR_SESSION_NOT_FOUND);
		}

		$this->output = $this->baseAdminModel->sessions;
	}

	public function create()
	{
		require_once $_SERVER['_HTDOCS_'] . '/base_classes/utility/srp.php';
		require_once $_SERVER['_HTDOCS_'] . '/environments/base/models/adminSessions.php';

		//Validate input
		{
			if (empty($this->baseAdminModel->email))
			{
				throw new error(errorCodes::ERROR_RESOURCE_NOT_FOUND);
			}

			if (empty($this->input['proof']))
			{
				throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
			}

			$create_data = array_merge(array('id' => $this->baseAdminModel->email), $this->input);
			$this->createValidation($create_data);
		}

		//Set session type
		if (isset($this->input['type']) && !in_array($this->input['type'], array_keys(baseSessionsModel::$sessionsPerType)))
		{
			throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
		}
		elseif (!isset($this->input['type']))
		{
			$this->input['type'] = baseAdminSessionsModel::SESSION_TYPE_WEBSITE;
		}

		$resourceType = strtolower($this->resourceLocation[3]);

		//Check if password is correct!
		if ($this->input['proof'] !== $this->baseAdminModel->srpfromReply($resourceType)->clientProof)
		{
			error::addError('Proof mismatch');
			throw new error(errorCodes::ERROR_PASSWORD_INCORRECT);
		}
                
		//Create session
		$this->baseAdminModel->sessions()->add($this->baseAdminModel->srp->serverSessionKey, $this->input['type']);

		$login = array(
			'ipLong' => ip2long(geoip::getClientIP()),
			'admin_id' => $this->baseAdminModel->admin_id,
                        'sessionKey' => $this->baseAdminModel->srp->serverSessionKey,
                        'geoip' => region::getClientRegion(),
		);
                
		if (!$this->baseAdminModel->sessions()->write('adminLogins', array(
				'action' => 'insert',
				'data' => $login,
			)))
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		$clientInfo = new clientInfo(clientInfo::CLIENTTYPE_ADMIN, $this->baseAdminModel->admin_id, 'base', $this->baseAdminModel->srp->serverSessionKey);
		$clientInfo->setAuthed();

		dataStore::setObject('clientInfo', $clientInfo);
		$this->runOptions(static::PRIMARY_MODEL);
		$clientInfo = dataStore::getObject('clientInfo');
		$this->output = $this->baseAdminModel;

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
			$this->baseAdminModel->clientSessions()->removeByKey($clientInfo->getSignature());
		}
		else
		{
			$this->baseAdminModel->clientSessions()->removeAll();
		}
	}
}