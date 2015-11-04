<?PHP
class baseAdminSessionsModel extends baseSessions
{

	const SESSION_TYPE_API = 'api';
	const SESSION_TYPE_WEBSITE = 'website';

	##
	# Properties
	##
	public $admin_id;
        public $sessions = array();
	public static $sessionsPerType = array(
		baseAdminSessionsModel::SESSION_TYPE_API => 10,
		baseAdminSessionsModel::SESSION_TYPE_WEBSITE => 1
	);

	##
	# Cache
	##
	public static $CACHE_KEY = array('admin_id');
	public static $COLLECTIONS = array('sessions');
	public static $cacheOwner = array(
		'type' => 'admin',
		'property' => 'admin_id',
	);

	const CACHE_EXPIRATION = 14400;
	const PARENT_MODEL = 'baseAdminModel';
        const BASE_MODEL = true;

	public function __construct($data = array())
	{
		require_once $_SERVER['_HTDOCS_'] . '/environments/base/models/adminSession.php';

		parent::__construct($data);
		if (!$this->fromCache)
		{
			if ($sessionList = $this->read('adminSessions', array(
					'data' => array(
						'admin_id' => $data['admin_id']
					)
				))
			)
			{
				foreach ($sessionList as $session)
				{
					$session['database'] = $this->database;
					$session['fromCache'] = true;

					$sessionModel = new baseAdminSessionModel($session);

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

		$session = baseAdminSessionModel::create($this, $sessionKey, $type);

		$this->updateSession($session);
		$this->trimByType($type);

		return $session;
	}
        
        /**
	 *
	 * @param baseAdminSessionModel $session
	 */
	public function removeSession(baseAdminSessionModel $session)
	{
		unset($this->sessions[$session->type . ':' . $session->sessionKey]);
		$session->destroySession();
		return true;
	}

        /**
	 *
	 * @param baseAdminSessionModel $session
	 */
	public function updateSession(baseAdminSessionModel $session)
	{
		$this->sessions[$session->type . ':' . $session->sessionKey] = $session;
		return true;
	}
        
}