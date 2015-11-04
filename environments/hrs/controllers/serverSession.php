<?PHP

class serverSession extends controller
{
        
	public $hrsServerModel;
        public $ownerModel;
        public $slaveid;
	const PRIMARY_MODEL = 'hrsServerModel';

	public $properties = array(
		'uid' => [
			'FILTER' => FILTER_VALIDATE_FLOAT,
			'RETURN_FUNCTION' => [
				'property' => self::PRIMARY_MODEL,
				'class' => self::PRIMARY_MODEL,
				'method' => 'fromUid',
				'includes' => '/environments/hrs/models/server.php',
			],
		],
                'slaveid' => [
                        'FILTER' => FILTER_VALIDATE_INT,
                ],
                'email' => [
			'FILTER' => FILTER_SANITIZE_STRING,
			'RETURN_FUNCTION' => [
				'property' => self::PRIMARY_MODEL,
				'class' => self::PRIMARY_MODEL,
				'method' => 'fromData',
				'includes' => '/environments/hrs/models/server.php',
                                'stringLookup' => 'email',
			],
		],
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
		if (empty($this->{self::PRIMARY_MODEL}->email))
		{
			throw new error(errorCodes::ERROR_RESOURCE_NOT_FOUND);
		}

		if (!$this->{self::PRIMARY_MODEL}->sessions())
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		if (empty($this->{self::PRIMARY_MODEL}->sessions()->sessions))
		{
			error::addError('This user has no session.');
			throw new error(errorCodes::ERROR_SESSION_NOT_FOUND);
		}

		$this->output = $this->{self::PRIMARY_MODEL}->sessions;
	}

	public function create()
	{
		require_once $_SERVER['_HTDOCS_'] . '/base_classes/utility/srp.php';
		require_once $_SERVER['_HTDOCS_'] . '/environments/hrs/models/serverSessions.php';
                
		//Validate input
		{
                        if (empty($this->{self::PRIMARY_MODEL}->email) || empty($this->slaveid) || $this->slaveid > $this->{self::PRIMARY_MODEL}->slaves)
			{
				throw new error(errorCodes::ERROR_RESOURCE_NOT_FOUND);
			}

			if (empty($this->input['proof']))
			{
				throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
			}

			$create_data = array_merge(array('id' => $this->{self::PRIMARY_MODEL}->email), $this->input);
			$this->createValidation($create_data);
		}
                
		//Set session type
		if (isset($this->input['type']) && !in_array($this->input['type'], array_keys(hrsServerSessionsModel::$sessionsPerType)))
		{
			throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
		}
		elseif (!isset($this->input['type']))
		{
			$this->input['type'] = hrsServerSessionsModel::SESSION_TYPE_API;
		}

		$resourceType = strtolower($this->resourceLocation[3]);

		//Check if password is correct!
		if ($this->input['proof'] !== $this->{self::PRIMARY_MODEL}->srpfromReply($resourceType)->clientProof)
		{
			error::addError('Proof mismatch');
			throw new error(errorCodes::ERROR_PASSWORD_INCORRECT);
		}
                
		//Create session
		$this->{self::PRIMARY_MODEL}->sessions($this->slaveid)->add($this->{self::PRIMARY_MODEL}->srp->serverSessionKey, $this->input['type']);

		$login = array(
                        'server_id' => $this->{self::PRIMARY_MODEL}->server_id,
			'ipLong' => ip2long(geoip::getClientIP()),
			'loginType' => $this->input['type'],
			'user_id' => $this->{self::PRIMARY_MODEL}->user_id,
			'slave_id' => $this->slaveid,
                        'sessionKey' => $this->{self::PRIMARY_MODEL}->srp->serverSessionKey,
                        'type' => $this->input['type'],
                        'geoip' => region::getClientRegion(),
		);
                
		if (!$this->{self::PRIMARY_MODEL}->write('hrsServerLogins', array(
				'action' => 'insert',
				'data' => $login,
			)))
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		$clientInfo = new clientInfo(clientInfo::CLIENTTYPE_CLIENT, $this->{self::PRIMARY_MODEL}->server_id, 'hrs', $this->{self::PRIMARY_MODEL}->srp->serverSessionKey);
		$clientInfo->setAuthed();

		dataStore::setObject('clientInfo', $clientInfo);
		$this->runOptions(static::PRIMARY_MODEL);
		$clientInfo = dataStore::getObject('clientInfo');
		$this->output = $this->{self::PRIMARY_MODEL};

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
			$this->{self::PRIMARY_MODEL}->clientSessions()->removeByKey($clientInfo->getSignature());
		}
		else
		{
			$this->{self::PRIMARY_MODEL}->clientSessions()->removeAll();
		}
	}
}