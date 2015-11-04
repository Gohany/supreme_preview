<?php

class frontendDomainModel extends model
{
        
        ##
        # Constants
        ##
        const BASE_DB = 'frontend';
        const OWNER_KEY = 'user_id';
        ##
        # Properties
        ##
        public $user_id;
        public $domain_id;
        public $name;
        public $date_created;
        
        ##
        # Objects
        ##
        public $forms;
        public $form;
        
        ##
	# Cache
	##
	public static $CACHE_KEY = array('user_id', 'domain_id');
        const CACHE_EXPIRATION = 3600;
	const PARENT_MODEL = 'frontendDomainsModel';
        
        public function __construct($data)
	{
                
                parent::__construct($data);
		if (!$this->fromCache)
		{
                        if (!$read = $this->read('domains', array('data' => array('user_id' => $this->user_id, 'domain_id' => $this->domain_id))))
                        {
                                return false;
                        }
                }
                
        }
        
        public static function fromDomain_id($domain_id)
        {
                return new frontendDomainModel(array('domain_id' => $domain_id));
        }
        
        public function forms()
        {
                require_once $_SERVER['_HTDOCS_'] . '/environments/frontend/models/forms.php';
                $this->forms || $this->forms = new frontendFormsModel(array('user_id' => $this->user_id, 'database' => $this->database));
                return $this->forms;
        }
        
        public function formByName($name)
        {
                $this->form || $this->form = $this->form()->formByName($name);
        }
        
        public function formById($id)
        {
                $this->form || $this->form = $this->form()->formByID($id);
        }
        
        public function changeName($newName)
        {
                
                $data = [
                    'name' => $newName,
                    'domain_id' => $this->domain_id,
                    'database' => $this->database,
                ];
                
                if (!$this->write('domains', array('action' => 'update', 'data' => $data)))
                {
                        throw new error(errorCodes::ERROR_INTERNAL_ERROR);
                }
                
                $this->name = $newName;
                return true;
                
        }
        
        public static function create($userModel, $input)
        {
                
                if ($userModel->domains()->domainByName($input['name']))
                {
                        error::addError('Domain already exists');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
                }
                
		$email = strtolower($input['name']);
		$data = [
			'name' => $input['name'],
                        'user_id' => $userModel->user_id,
                        'database' => $userModel->database,
		];
                
                $domain = new frontendDomainModel($data);

		if (!$domain_id = $domain->write('domains', array(
				'action' => 'insert',
				'data' => $data
			))
		)
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		$domain->domain_id = $domain_id;
                $userModel->domains()->update($domain);
                
		return $domain;
	
        }
        
}