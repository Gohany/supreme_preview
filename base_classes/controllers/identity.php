<?php

class identityController extends controller
{
        
        public $ownerModel;
        
        public function display()
	{
		if (!is_object($this->{static::PRIMARY_MODEL}))
		{
			throw new error(errorCodes::ERROR_ACCOUNT_NOT_FOUND);
		}
		$this->runOptions(static::PRIMARY_MODEL);
		$this->output = $this->{static::PRIMARY_MODEL};
	}
        
        public function modify()
	{
                
		if (empty($this->{static::PRIMARY_MODEL}) || empty($this->input['action']))
		{
			throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
		}
		switch ($this->input['action'])
		{
			case 'setEmail':
				
                                if (method_exists($this->{static::PRIMARY_MODEL}, 'changeEmail'))
                                {
                                        if (!isset($this->input['email']) || !valid::email($this->input['email']))
                                        {
                                                throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
                                        }

                                        $this->{static::PRIMARY_MODEL}->changeEmail($this->input['email']);
                                }
				break;
			
			case 'setPassword':
                                if (method_exists($this->{static::PRIMARY_MODEL}, 'changePassword'))
                                {
                                        //Authenticate request
                                        if (empty($this->input['proof']) || !valid::hex($this->input['proof']))
                                        {
                                                error::addError('Invalid proof.');
                                                throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
                                        }

                                        if (!isset($this->resourceLocation[3]))
                                        {
                                                error::addError("Resource must be specified.");
                                                throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
                                        }
                                        $srpType = strtolower($this->resourceLocation[3]);

                                        $this->{static::PRIMARY_MODEL}->srpfromReply($srpType);
                                        if ($this->input['proof'] != $this->{static::PRIMARY_MODEL}->srp->clientProof)
                                        {
                                                throw new error(errorCodes::ERROR_PASSWORD_INCORRECT);
                                        }

                                        //Do Request
                                        if (!isset($this->input['newPassword']))
                                        {
                                                throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
                                        }

                                        $this->{static::PRIMARY_MODEL}->changePassword($this->input['newPassword']);
                                }
				break;
			case 'adminSetEmail':
                                if (method_exists($this->{static::PRIMARY_MODEL}, 'changeEmail'))
                                {
                                        if (!isset($this->input['newEmail']) || !valid::email($this->input['newEmail']))
                                        {
                                                throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
                                        }

                                        $this->{static::PRIMARY_MODEL}->changeEmail($this->input['newEmail']);
                                }
				break;
			case 'adminSetPassword':
                                if (method_exists($this->{static::PRIMARY_MODEL}, 'changePassword'))
                                {
                                        if (!isset($this->input['newPassword']))
                                        {
                                                throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
                                        }

                                        $this->{static::PRIMARY_MODEL}->changePassword($this->input['newPassword']);
                                }
				break;
			default:
				throw new error(errorCodes::ERROR_ACTION_NOT_FOUND);
		}

		$this->output = $this->{static::PRIMARY_MODEL};
	}
        
        public function create()
	{
		$this->createValidation($this->input);
                
                $static = static::PRIMARY_MODEL;
                
                $this->{static::PRIMARY_MODEL} = $static::create($this->input, $this->ownerModel);

		//Set as authed client
		{
                        $clientInfo = new clientInfo(clientInfo::CLIENTTYPE_CLIENT);
			$clientInfo->setAuthed();
			dataStore::setObject('clientInfo', $clientInfo);
		}

		$this->output =  $this->{static::PRIMARY_MODEL};
	}
        
}