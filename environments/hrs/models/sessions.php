<?PHP
class hrsSessionsModel extends baseSessions
{

	const SESSION_TYPE_API = 'api';
	const SESSION_TYPE_WEBSITE = 'website';

	##
	# Properties
	##
	public $account_id;
        public $user_id;
        public $database;
        public $sessions = array();
        
	public static $sessionsPerType = array(
		hrsSessionsModel::SESSION_TYPE_API => 10,
		hrsSessionsModel::SESSION_TYPE_WEBSITE => 1
	);

	##
	# Cache
	##
	public static $CACHE_KEY = array('account_id');
	public static $COLLECTIONS = array('sessions');
	public static $cacheOwner = array(
		'type' => 'user',
		'property' => 'account_id',
	);

        const OWNER_KEY = 'user_id';
        const BASE_DB = 'hrs';
	const CACHE_EXPIRATION = 14400;
	const PARENT_MODEL = 'hrsAccountModel';

	public function __construct($data = array())
	{
                
		require_once $_SERVER['_HTDOCS_'] . '/environments/hrs/models/session.php';

		parent::__construct($data);
		if (!$this->fromCache)
		{
			if ($sessionList = $this->read('hrsSessions', array(
					'data' => array(
						'account_id' => $data['account_id']
					)
				))
			)
			{
				foreach ($sessionList as $session)
				{
					$session['database'] = $this->database;
					$session['fromCache'] = true;

					$sessionModel = new hrsSessionModel($session);

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
		require_once $_SERVER['_HTDOCS_'] . '/environments/hrs/models/session.php';

		$session = hrsSessionModel::create($this, $sessionKey, $type);

		$this->updateSession($session);
		$this->trimByType($type);

		return $session;
	}

	/**
	 *
	 * @param hrsSessionModel $session
	 */
	public function removeSession(hrsSessionModel $session)
	{
		unset($this->sessions[$session->type . ':' . $session->sessionKey]);
		$session->destroySession();
		return true;
	}

	/**
	 *
	 * @param hrsSessionModel $session
	 */
	public function updateSession(hrsSessionModel $session)
	{
		$this->sessions[$session->type . ':' . $session->sessionKey] = $session;
		return true;
	}
        
}