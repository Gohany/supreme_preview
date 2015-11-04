<?PHP
class baseSessionsModel extends baseSessions
{

	const SESSION_TYPE_API = 'api';
	const SESSION_TYPE_WEBSITE = 'website';

	##
	# Properties
	##
	public $user_id;
        public $sessions = array();
	public static $sessionsPerType = array(
		baseSessionsModel::SESSION_TYPE_API => 10,
		baseSessionsModel::SESSION_TYPE_WEBSITE => 1
	);

	##
	# Cache
	##
	public static $CACHE_KEY = array('user_id');
	public static $COLLECTIONS = array('sessions');
	public static $cacheOwner = array(
		'type' => 'user',
		'property' => 'user_id',
	);

	const CACHE_EXPIRATION = 14400;
	const PARENT_MODEL = 'baseUserModel';
        const BASE_MODEL = true;

	public function __construct($data = array())
	{
		require_once $_SERVER['_HTDOCS_'] . '/environments/base/models/session.php';

		parent::__construct($data);
		if (!$this->fromCache)
		{
			if ($sessionList = $this->read('sessions', array(
					'data' => array(
						'user_id' => $data['user_id']
					)
				))
			)
			{
				foreach ($sessionList as $session)
				{
					$session['database'] = $this->database;
					$session['fromCache'] = true;

					$sessionModel = new baseSessionModel($session);

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
		require_once $_SERVER['_HTDOCS_'] . '/environments/base/models/session.php';

		$session = baseSessionModel::create($this, $sessionKey, $type);

		$this->updateSession($session);
		$this->trimByType($type);

		return $session;
	}

	/**
	 *
	 * @param baseSessionModel $session
	 */
	public function removeSession(baseSessionModel $session)
	{
		unset($this->sessions[$session->type . ':' . $session->sessionKey]);
		$session->destroySession();
		return true;
	}

	/**
	 *
	 * @param baseSessionModel $session
	 */
	public function updateSession(baseSessionModel $session)
	{
		$this->sessions[$session->type . ':' . $session->sessionKey] = $session;
		return true;
	}
        
}