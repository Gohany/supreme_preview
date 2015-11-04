<?PHP
class hrsServerSessionsModel extends baseSessions
{

	const SESSION_TYPE_API = 'api';
	const SESSION_TYPE_WEBSITE = 'website';

	##
	# Properties
	##
	public $server_id;
        public $user_id;
        public $slave_id;
        public $database;
        public $sessions = array();
        
	public static $sessionsPerType = array(
		hrsServerSessionsModel::SESSION_TYPE_API => 1,
	);

	##
	# Cache
	##
	public static $CACHE_KEY = array('server_id','slave_id');
	public static $COLLECTIONS = array('sessions');
	public static $cacheOwner = array(
		'type' => 'user',
		'property' => 'server_id',
	);

        const OWNER_KEY = 'user_id';
        const BASE_DB = 'hrs';
	const CACHE_EXPIRATION = 14400;
	const PARENT_MODEL = 'hrsServerModel';

	public function __construct($data = array())
	{
                
		require_once $_SERVER['_HTDOCS_'] . '/environments/hrs/models/serverSession.php';

		parent::__construct($data);
		if (!$this->fromCache)
		{
                        
			if ($sessionList = $this->read('hrsServerSessions', array(
					'data' => array(
						'server_id' => $data['server_id'],
                                                'slave_id' => $data['slave_id'],
					)
				))
			)
			{
				foreach ($sessionList as $session)
				{
					$session['database'] = $this->database;
					$session['fromCache'] = true;

					$sessionModel = new hrsServerSessionModel($session);

					if ($sessionModel->isExpired())
					{
						$sessionModel->destroySession();
					}
					else
					{
						$this->updateSession($sessionModel);
						$this->trimByType($sessionModel->type);
					}
				}
			}
		}
		else
		{
                        
			foreach ($this->sessions as $session)
			{
				if ($session->isExpired())
				{
					$this->removeSession($session);
				}
			}
		}
                
	}

	public function add($sessionKey, $type)
	{
		require_once $_SERVER['_HTDOCS_'] . '/environments/hrs/models/serverSession.php';

		$session = hrsServerSessionModel::create($this, $sessionKey, $type);
		$this->updateSession($session);
                $this->trimByType($type);
                
		return $session;
	}

	/**
	 *
	 * @param hrsServerSessionModel $session
	 */
	public function removeSession(hrsServerSessionModel $session)
	{
		unset($this->sessions[$session->type . ':' . $session->sessionKey]);
		$session->destroySession();
		return true;
	}

	/**
	 *
	 * @param hrsServerSessionModel $session
	 */
	public function updateSession(hrsServerSessionModel $session)
	{
		$this->sessions[$session->type . ':' . $session->sessionKey] = $session;
		return true;
	}
        
}