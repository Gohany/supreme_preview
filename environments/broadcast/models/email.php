<?php

class broadcastEmailModel extends model
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
        public $user_id;
	public $email_id;
	public $email;
	public $firstName;
	public $lastName;
	public $date_created;
        public $dataSets = array('broadcast');

	##
	# Cache
	##
	const CACHE_EXPIRATION = 3600;

	public $doNotCache = array();
	public static $CACHE_KEY = array('email_id');
	public static $cacheOwner = array(
		'type' => 'email',
		'property' => 'email_id',
	);
        
        public function __construct(array $data = array())
	{
		parent::__construct($data);
		if ($this->fromCache)
		{
			return;
		}
                
                if (!$read = $this->read('users', array('data' => array('user_id' => $this->user_id))))
		{
			throw new error(errorCodes::ERROR_ACCOUNT_NOT_FOUND);
		}

		$this->loadProperties($read);
	}
        
        public function lists()
        {
                
        }
        
        public static function create(baseUserModel $userModel, $input)
	{
		
                if (!$emailList = $userModel->emailLists()->emailListByName($input['list']))
                {
                        error::addError('Invalid list');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
                }
                if ($emailList->emailExists($input['email']))
                {
                        error::addError($email . ' already in use.');
			throw new error(errorCodes::ERROR_EMAIL_IN_USE);
                }
                
		$email = strtolower($input['email']);
		$data = [
			'email' => $input['email'],
			'firstName' => $input['firstName'],
                        'lastName' => $input['lastName'],
		];

		if (!$email_id = $userModel->write('email', array(
				'action' => 'insert',
				'data' => $data
			))
		)
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		$data['email_id'] = $email_id;
		$baseEmailModel = new baseEmailModel($data);

		return $baseEmailModel;
	}
        
}