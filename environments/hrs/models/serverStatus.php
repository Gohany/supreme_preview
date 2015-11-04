<?php

class hrsServerStatusModel extends model
{
        
        ##
	# Constants
	##
        const OWNER_ID = 'user_id';
        const BASE_DB = 'hrs';
        const OWNER_KEY = 'user_id';
        const PRIMARY_ID = 'server_id';
        
        ##
	# Properties
	##
	public $server_id;
        public $user_id;
        public $status;
        public $localTime;
        public $actions;
        public $date_created;

	##
	# Cache
	##
	const CACHE_EXPIRATION = 3600;
        
	public $doNotCache = array();
	public static $CACHE_KEY = array('server_id');
	public static $cacheOwner = array(
		'type' => 'user',
		'property' => 'user_id',
	);
        
        public function __construct(array $data = array())
	{
		parent::__construct($data);
		if ($this->fromCache)
		{
                        $this->setDBs();
			return;
		}
                
                if (!$read = $this->read('hrsServerStatus', array('data' => array('server_id' => $this->server_id))))
                {
                        throw new error(errorCodes::ERROR_ACCOUNT_NOT_FOUND);
                }
                
		$this->loadProperties($read);
                $this->setDBs();
	}
        
        public static function create($input, hrsServerModel $ownerObject)
	{
                
		$data = [
                        'user_id' => $ownerObject->user_id,
                        'server_id' => $ownerObject->server_id,
			'status' => $input['status'],
			'localTime' => $input['localTime'],
                        'actions' => $input['actions'],
                        'fromCache' => true,
                        'database' => $ownerObject->database,
		];
                
                if (!$ownerObject->write('hrsServerStatus', array(
				'action' => 'insert',
				'data' => $data
			))
		)
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}
                
                // don't throw in data, make it query so we get the date_created
		return new hrsServerStatusModel(array('database' => $ownerObject->database, 'server_id' => $ownerObject->server_id));
	}
        
}