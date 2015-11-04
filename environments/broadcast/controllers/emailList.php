<?php

class emailList extends controller
{
        
        public $broadcastEmailModel;
        public $baseUserModel;
	const PRIMARY_MODEL = 'broadcastEmailListModel';

	public $properties = [
                'userid' => [
			'FILTER' => FILTER_VALIDATE_INT,
			'RETURN_FUNCTION' => [
				'property' => 'baseUserModel',
				'class' => 'baseUserModel',
				'method' => 'fromUser_id',
				'includes' => '/environments/base/models/user.php',
			],
		],
		'listid' => [
			'FILTER' => FILTER_VALIDATE_INT,
			'RETURN_FUNCTION' => [
				'object_property' => 'baseUserModel',
				'class' => self::PRIMARY_MODEL,
				'method' => 'emailLists',
                                'subObjectMethod' => 'fromEmailList_id',
				'includes' => '/environments/broadcast/models/emailList.php',
			],
		],
                'name' => [
			'FILTER' => FILTER_SANITIZE_STRING,
			'RETURN_FUNCTION' => [
				'object_property' => 'baseUserModel',
				'class' => self::PRIMARY_MODEL,
				'method' => 'fromEmailName',
				'includes' => '/environments/broadcast/models/emailList.php',
			],
		],
	];
        
        public $create_data = [
		'name' => [
			'RETURN_FUNCTION' => [
				'class' => 'valid',
				'method' => 'realName'
			],
			'ERROR_CODE' => errorCodes::ERROR_EMAIL_INVALID
		],
	];
        
        public function display()
	{
		$this->runOptions(static::PRIMARY_MODEL);
		$this->output = $this->baseUserModel->emailList;
	}
        
        public function modify()
	{
                
		if (empty($this->broadcastEmailModel) || empty($this->broadcastEmailModel->email) || empty($this->input['action']))
		{
			throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
		}
		switch ($this->input['action'])
		{
			case 'setNames':
				$this->broadcastEmailModel->setNames($this->input['firstName'], $this->input['lastName']);
				break;
			case 'addGroup':
				$this->broadcastEmailModel->addGroup($this->input['group']);
				break;
                        case 'removeGroup':
                                $this->broadcastEmailModel->removeGroup($this->input['group']);
                                break;
			default:
				throw new error(errorCodes::ERROR_ACTION_NOT_FOUND);
		}

		$this->output = $this->broadcastEmailModel;
	}
        
        public function create()
	{
		$this->createValidation($this->input);
                
		require_once $_SERVER['_HTDOCS_'] . '/environments/broadcast/models/emailList.php';
                $this->{self::PRIMARY_MODEL} = broadcastEmailListModel::create($this->baseUserModel, $this->input);
		$this->output =  $this->{self::PRIMARY_MODEL};
	}
        
}