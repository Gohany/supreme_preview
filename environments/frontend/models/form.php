<?php

class frontendFormModel extends model
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
        public $form_id;
        public $name;
        public $data;
        public $date_created;
        
        ##
	# Cache
	##
	public static $CACHE_KEY = array('user_id', 'form_id');
        const CACHE_EXPIRATION = 3600;
	const PARENT_MODEL = 'frontendFormsModel';
        
        public function __construct($data)
	{
                
                parent::__construct($data);
		if (!$this->fromCache)
		{
                        if (!$read = $this->read('forms', array('data' => array('user_id' => $this->user_id, 'form_id' => $this->form_id))))
                        {
                                return false;
                        }
                }
                
        }
        
        public function setData($newData)
        {
               
                $data = [
                    'data' => $newData,
                    'form_id' => $this->form_id,
                    'database' => $this->database,
                ];
                
                if (!$this->write('forms', array('action' => 'update', 'data' => $data)))
                {
                        throw new error(errorCodes::ERROR_INTERNAL_ERROR);
                }
                
                $this->data = $newData;
                return true;
                
        
        }
        
        public static function create($domainModel, $input)
        {
                
                if ($domainModel->forms()->formByName($input['name']))
                {
                        error::addError('Form already exists');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
                }
                
		$email = strtolower($input['name']);
		$data = [
			'name' => $input['name'],
                        'user_id' => $domainModel->user_id,
                        'domain_id' => $input['domainid'],
                        'database' => $domainModel->database,
                        'data' => $input['data'],
		];
                
                $form = new frontendFormModel($data);
                
		if (!$form_id = $form->write('forms', array(
				'action' => 'insert',
				'data' => $data
			))
		)
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}
                
		$form->form_id = $form_id;
                $domainModel->forms()->update($form);
                
		return $form;
	
        }
        
}