<?PHP
class baseAdminSessionModel extends baseSession
{
	##
	# Properties
	##
        public $admin_id;
	##
	# Cache
	##
	public static $CACHE_KEY = array('sessionKey');
	public static $cacheOwner = array(
		'type' => 'admin',
		'property' => 'admin_id',
	);

	const CACHE_EXPIRATION = 14400;
	const PARENT_MODEL = 'baseAdminSessionsModel';
        const BASE_MODEL = true;
        const ITERATION_SALT = 'BITao0rndml62u4MHS0gYQT9OpiOHtsYybixo6SK';

	public function __construct($data = array())
	{

		parent::__construct($data);
		if (!$this->fromCache)
		{
			if ($session = $this->read('adminSessions', array(
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
			'admin_id' => $sessionsModel->admin_id,
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

		if (!$sessionsModel->write('adminSessions', array(
				'action' => 'insert',
				'data' => $data
			))
		)
		{
			error::addError('Failed to add session');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}
                
		$data['fromCache'] = true;

		return new baseAdminSessionModel($data);
	}

	public function destroySession()
	{
		if ($this->sessionKey !== null && !$this->write('adminSessions', array(
				'action' => 'delete',
				'data' => array(
					'admin_id' => $this->admin_id,
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
                
                if (!$this->write('adminSessions', array(
				'action' => 'update',
				'data' => array('admin_id' => $this->admin_id, 'sessionKey' => $this->sessionKey, 'sessionData' => $this->sessionData)
			))
		)
		{
			error::addError('Failed to write to session');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}
                return true;
                
        }

}