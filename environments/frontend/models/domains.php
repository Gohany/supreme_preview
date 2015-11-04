<?php

class frontendDomainsModel extends model
{
        
        ##
        # Constants
        ##
        const PARENT_MODEL = 'baseUserModel';
        const BASE_DB = 'frontend';
        const OWNER_KEY = 'user_id';
        
        ##
        # Properties
        ##
        public $domains;
        public $domainsByID;
        public $domainsByName;
        public $user_id;
        
        ##
	# Cache
	##
	const CACHE_EXPIRATION = 3600;
        #public static $COLLECTIONS = array('emailLists');
        public static $CACHE_KEY = array('user_id');
        
        public function __construct(array $data = array())
	{
                require_once $_SERVER['_HTDOCS_'] . '/environments/frontend/models/domain.php';
		parent::__construct($data);
		if (!$this->fromCache)
		{
                        
                        if (!$read = $this->read('domains', array('data' => array('user_id' => $this->user_id))))
                        {
                                return false;
                        }

                        foreach ($read as $domains)
                        {
                                $domains['fromCache'] = true;
                                $this->domains[$domains['domain_id']] = new frontendDomainModel($domains);
                        }
                
                }
                $this->reloadCategories();
                
	}
        
        public function update(frontendDomainModel $domain)
	{
		$this->domains[$domain->domain_id] = $domain;
	}
        
        public function domainByID($domain_id)
	{
		if (!isset($this->domainsByID[$domain_id]))
		{
			return false;
		}

		return $this->domainsByID[$domain_id];
	}

	public function domainByName($domain)
	{
		if (!isset($this->domainsByName[$domain]))
		{
			return false;
		}

		return $this->domainsByName[$domain];
	}
        
        public function reloadCategories()
	{
		$this->domainsByID = array();
		$this->domainsByName = array();

		foreach ($this->domains as $domain)
		{
			$this->domainsByID[$domain->domain_id] = $domain;
			$this->domainsByName[$domain->name] = $domain;
		}
	}
        
}