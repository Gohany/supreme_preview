<?php

class serverStatus extends controller
{
        
        public $hrsServerModel;
        public $ownerModel;
	const PRIMARY_MODEL = 'hrsServerModel';

	public $properties = [
		'uid' => [
			'FILTER' => FILTER_VALIDATE_FLOAT,
			'RETURN_FUNCTION' => [
				'property' => self::PRIMARY_MODEL,
				'class' => self::PRIMARY_MODEL,
				'method' => 'fromUid',
				'includes' => '/environments/hrs/models/server.php',
			],
		],
		'email' => [
			'FILTER' => FILTER_SANITIZE_STRING,
			'RETURN_FUNCTION' => [
				'property' => self::PRIMARY_MODEL,
				'class' => self::PRIMARY_MODEL,
				'method' => 'fromData',
				'includes' => '/environments/hrs/models/server.php',
                                'stringLookup' => 'email',
			],
		],
	];
        
        public $create_data = [
		'status' => [
			'RETURN_FUNCTION' => [
				'class' => 'valid',
				'method' => 'string'
			],
		],
		'localTime' => [
			'RETURN_FUNCTION' => [
				'class' => 'valid',
				'method' => 'increment'
			],
                        'optional' => true
		],
                'actions' => [
			'RETURN_FUNCTION' => [
				'class' => 'valid',
				'method' => 'increment'
			],
			'optional' => true
		],
	];
        
        public function display()
	{
		if (!is_object($this->{static::PRIMARY_MODEL}))
		{
			throw new error(errorCodes::ERROR_ACCOUNT_NOT_FOUND);
		}
                $this->{static::PRIMARY_MODEL}->status();
		$this->runOptions(static::PRIMARY_MODEL);
		$this->output = $this->{static::PRIMARY_MODEL};
	}
        
        public function create()
	{
		$this->createValidation($this->input);
                $this->{static::PRIMARY_MODEL}->status = hrsServerStatusModel::create($this->input, $this->{static::PRIMARY_MODEL});
		$this->output =  $this->{static::PRIMARY_MODEL};
	}
        
}