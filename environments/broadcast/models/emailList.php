<?php

class broadcastEmailListModel extends model
{
        
        ##
        # Constants
        ##
        const BASE_DB = 'broadcast';
        const OWNER_KEY = 'user_id';
        
        ##
        # Properties
        ##
        public $user_id;
        public $emailList_id;
        public $name;
        public $date_created;
        
        ##
	# Cache
	##
	public static $CACHE_KEY = array('user_id', 'emailList_id');
        const CACHE_EXPIRATION = 3600;
	const PARENT_MODEL = 'broadcastEmailListsModel';
        
        public function __construct($data)
	{
                
                parent::__construct($data);
		if (!$this->fromCache)
		{
                        if (!$read = $this->read('emailLists', array('data' => array('user_id' => $this->user_id, 'emailList_id' => $this->emailList_id))))
                        {
                                return false;
                        }
                }
                
        }
        
        public static function create($userModel, $input)
        {
                
                if ($userModel->emailLists()->emailListByName($input['name']))
                {
                        error::addError('List already exists');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
                }
                
		$email = strtolower($input['name']);
		$data = [
			'name' => $input['name'],
                        'user_id' => $userModel->user_id,
                        'database' => $userModel->database,
		];
                
                $emailList = new broadcastEmailListModel($data);

		if (!$emailList_id = $emailList->write('emailLists', array(
				'action' => 'insert',
				'data' => $data
			))
		)
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		$emailList->emailList_id = $emailList_id;
                $userModel->emailLists()->update($emailList);
                
		return $emailList;
	
        }
        
}