<?php

class broadcastEmailListsModel extends model
{
        
        ##
        # Constants
        ##
        const PARENT_MODEL = 'baseUserModel';
        const BASE_DB = 'broadcast';
        const OWNER_KEY = 'user_id';
        
        ##
        # Properties
        ##
        public $emailLists;
        public $emailListByID;
        public $emailListByName;
        public $user_id;
        
        ##
	# Cache
	##
	const CACHE_EXPIRATION = 3600;
        #public static $COLLECTIONS = array('emailLists');
        public static $CACHE_KEY = array('user_id');
        
        public function __construct(array $data = array())
	{
                require_once $_SERVER['_HTDOCS_'] . '/environments/broadcast/models/emailList.php';
		parent::__construct($data);
		if (!$this->fromCache)
		{
                        
                        if (!$read = $this->read('emailLists', array('data' => array('user_id' => $this->user_id))))
                        {
                                return false;
                        }

                        foreach ($read as $emailList)
                        {
                                $emailList['fromCache'] = true;
                                $this->emailLists[$emailList['emailList_id']] = new broadcastEmailListModel($emailList);
                        }
                
                }
                $this->reloadCategories();
                
	}
        
        public function update(broadcastEmailListModel $emailList)
	{
		$this->emailLists[$emailList->emailList_id] = $emailList;
	}
        
        public function emailListByID($emailListId)
	{
		if (!isset($this->emailListByID[$emailListId]))
		{
			return false;
		}

		return $this->emailListByID[$emailListId];
	}

	public function emailListByName($emailListName)
	{
		if (!isset($this->emailListByName[$emailListName]))
		{
			return false;
		}

		return $this->emailListByName[$emailListName];
	}
        
        public function reloadCategories()
	{
		$this->emailListByID = array();
		$this->emailListByName = array();

		foreach ($this->emailLists as $emailList)
		{
			$this->emailListByID[$emailList->emailList_id] = $emailList;
			$this->emailListByName[$emailList->name] = $emailList;
		}
	}
        
}