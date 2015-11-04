<?php

class emailLists extends controller
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
	];
        
        public function display()
	{
                $this->baseUserModel->emailLists();
		$this->runOptions(static::PRIMARY_MODEL);
		$this->output = $this->baseUserModel->emailLists;
	}
        
}