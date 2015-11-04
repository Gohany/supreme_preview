<?php

class frontendFormsModel extends model
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
        public $forms;
        public $formsByID;
        public $formsByName;
        public $formsByDomain_id;
        public $user_id;
        public $domain_id;
        
        ##
	# Cache
	##
	const CACHE_EXPIRATION = 3600;
        #public static $COLLECTIONS = array('emailLists');
        public static $CACHE_KEY = array('user_id');
        
        public function __construct(array $data = array())
	{
                
                require_once $_SERVER['_HTDOCS_'] . '/environments/frontend/models/form.php';
		parent::__construct($data);
		if (!$this->fromCache)
		{
                        
                        if (!$read = $this->read('forms', array('data' => array('user_id' => $this->user_id))))
                        {
                                return false;
                        }

                        foreach ($read as $forms)
                        {
                                $forms['fromCache'] = true;
                                $this->forms[$forms['form_id']] = new frontendFormModel($forms);
                        }
                
                }
                $this->reloadCategories();
                
	}
        
        public function update(frontendFormModel $form)
	{
		$this->forms[$form->form_id] = $form;
	}
        
        public function formByID($form_id)
	{
		if (!isset($this->formsByID[$form_id]))
		{
			return false;
		}

		return $this->formsByID[$form_id];
	}

	public function formByName($form)
	{
		if (!isset($this->formsByName[$form]))
		{
			return false;
		}

		return $this->formsByName[$form];
	}
        
        public function formsByDomain_id($domain_id)
        {
                if (!isset($this->formsByDomain_id[$domain_id]))
                {
                        return false;
                }
                
                return $this->formsByDomain_id[$domain_id];
        }
        
        public function reloadCategories()
	{
		$this->formsByID = array();
		$this->formsByName = array();
                $this->formsByDomain_id = array();

		foreach ($this->forms as $form)
		{
			$this->formsByID[$form->form_id] = $form;
			$this->formsByName[$form->name] = $form;
                        $this->formsByDomain_id[$form->domain_id][] = $form;
		}
	}
        
}