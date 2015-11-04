<?PHP
class hrsSessionModel extends baseSession
{
	##
	# Properties
	##
        public $account_id;
        public $user_id;
	##
	# Cache
	##
	public static $CACHE_KEY = array('sessionKey');
	public static $cacheOwner = array(
		'type' => 'account',
		'property' => 'account_id',
	);

        const OWNER_KEY = 'user_id';
        const BASE_DB = 'hrs';
	const CACHE_EXPIRATION = 14400;
	const PARENT_MODEL = 'hrsSessionsModel';
        const ITERATION_SALT = 'AHDAl8zagO4mAJNH4Aukz8M2G7dF9e5CvkUMWYpl';

	public function __construct($data = array())
	{

		parent::__construct($data);
		if (!$this->fromCache)
		{
			if ($session = $this->read('hrsSessions', array(
					'data' => $data,
					'limit' => 1
				))
			)
			{
				$this->loadProperties($session);
			}
		}
	}

	public static function create($sessionsModel, $sessionKey, $type)
	{

		
		$ip = geoip::getClientIp();
                
		$data = array(
                        'account_id' => $sessionsModel->account_id,
			'user_id' => $sessionsModel->user_id,
			'sessionKey' => $sessionKey,
			'type' => $type,
			'ip' => ip2long(geoip::getClientIP()),
			'clientData' => array(
				'region' => region::getClientRegion(),
				'ip' => $ip,
			),
			'expiration' => date('Y-m-d H:i:s', time() + static::CACHE_EXPIRATION)
		);

		//Setup clientData
		{
			$geoIP = new geoip(geoip::getClientIP());
			if ($geoIP->hasGeoData())
			{
				$data['clientData']['longitude'] = $geoIP->getLongitude();
				$data['clientData']['latitude'] = $geoIP->getLatitude();
				$data['clientData']['country'] = $geoIP->getCountryCode();
			}
		}

		if (!$sessionsModel->write('hrsSessions', array(
				'action' => 'insert',
				'data' => $data
			))
		)
		{
			error::addError('Failed to add session');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}
                
		$data['fromCache'] = true;

		return new hrsSessionModel($data);
	}

	public function destroySession()
	{
		if ($this->sessionKey !== null && !$this->write('hrsSessions', array(
				'action' => 'delete',
				'data' => array(
					'account_id' => $this->account_id,
					'sessionKey' => $this->sessionKey
				)
			))
		)
		{
			error::addError('Failed to add session');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		$this->deleteCache();
	}
        
        public function saveSessionData()
        {
                
                if (!$this->write('hrsSessions', array(
				'action' => 'update',
				'data' => array('account_id' => $this->account_id, 'sessionKey' => $this->sessionKey, 'sessionData' => $this->sessionData)
			))
		)
		{
			error::addError('Failed to write to session');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}
                return true;
                
        }
        
}