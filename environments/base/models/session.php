<?PHP
class baseSessionModel extends baseSession
{
	##
	# Properties
	##
        public $user_id;
	##
	# Cache
	##
	public static $CACHE_KEY = array('sessionKey');
	public static $cacheOwner = array(
		'type' => 'user',
		'property' => 'user_id',
	);

	const CACHE_EXPIRATION = 14400;
	const PARENT_MODEL = 'baseSessionsModel';
        const BASE_MODEL = true;
        const ITERATION_SALT = 'AHDAl8zagO4mAJNH4Aukz8M2G7dF9e5CvkUMWYpl';

	public function __construct($data = array())
	{

		parent::__construct($data);
		if (!$this->fromCache)
		{
			if ($session = $this->read('sessions', array(
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

		if (!$sessionsModel->write('sessions', array(
				'action' => 'insert',
				'data' => $data
			))
		)
		{
			error::addError('Failed to add session');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}
                
		$data['fromCache'] = true;

		return new baseSessionModel($data);
	}

	public function destroySession()
	{
		if ($this->sessionKey !== null && !$this->write('sessions', array(
				'action' => 'delete',
				'data' => array(
					'user_id' => $this->user_id,
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
                
                if (!$this->write('sessions', array(
				'action' => 'update',
				'data' => array('user_id' => $this->admin_id, 'sessionKey' => $this->sessionKey, 'sessionData' => $this->sessionData)
			))
		)
		{
			error::addError('Failed to write to session');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}
                return true;
                
        }
        
}