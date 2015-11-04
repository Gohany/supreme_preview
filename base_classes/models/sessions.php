<?php
class baseSessions extends model
{

	public function removeByKey($sessionKey)
	{
		if (empty($this->sessions))
		{
			return;
		}

		foreach ($this->sessions as $session)
		{
			if ($session->sessionKey === $sessionKey)
			{
				$this->removeSession($session);
				break;
			}
		}
	}

	public function removeAll()
	{
		if (empty($this->sessions))
		{
			return;
		}

		foreach ($this->sessions as $session)
		{
			$this->removeSession($session);
		}
	}
        
        public function isValidIteration($signature, $authToken)
        {
                
                if (empty($this->sessions))
		{
			error::addError('Session key does not match.');
			throw new error(errorCodes::ERROR_SESSION_INVALID);
		}
                
                foreach ($this->sessions as $session)
		{
			if (($session->sessionKey === null || $session->signatureIteration($authToken) == $signature) && !$session->isExpired())
			{
                                $session->sessionData['validatedRequests']++;
                                $session->saveSessionData();
				return true;
			}
		}
                
                error::addError('Session key does not match.');
		throw new error(errorCodes::ERROR_SESSION_INVALID);
                
                
        }

	public function isValidSession($sessionKey)
	{
		if (empty($this->sessions))
		{
			error::addError('Session key does not match.');
			throw new error(errorCodes::ERROR_SESSION_INVALID);
		}

		foreach ($this->sessions as $session)
		{
			if (($session->sessionKey === null || $session->sessionKey === $sessionKey) && !$session->isExpired())
			{
				return true;
			}
		}

		error::addError('Session key does not match.');
		throw new error(errorCodes::ERROR_SESSION_INVALID);
	}
        
        public function isValidAuthToken($signature, $authToken)
        {
                
                if (empty($this->sessions))
		{
			error::addError('Session key does not match.');
			throw new error(errorCodes::ERROR_SESSION_INVALID);
		}
                
                foreach ($this->sessions as $session)
		{
			if (!$session->isExpired() && $session->signature($authToken) == $signature)
			{
				return true;
			}
		}

		error::addError('Session key does not match.');
		throw new error(errorCodes::ERROR_SESSION_INVALID);
                
        }

	public function validChallenge($challenge, $hash)
	{
		if (empty($this->sessions))
		{
			error::addError("Challenge denied.");
			throw new error(errorCodes::ERROR_CHALLENGE_BAD);
		}

		foreach ($this->sessions as $session)
		{
			if (($session->sessionKey === null || utility::hmac($challenge, $session->sessionKey) === $hash) && !$session->isExpired())
			{
				return $session;
			}
		}

		error::addError("Challenge denied.");
		throw new error(errorCodes::ERROR_CHALLENGE_BAD);
	}

	/**
	 *
	 * @param $type
	 * @return bool
	 */
	public function trimByType($type)
	{
		if (empty($this->sessions) || !isset(static::$sessionsPerType[$type]))
		{
			return;
		}

		//Generate counts
		$count = 0;
		foreach ($this->sessions as $session)
		{
			if ($session->type == $type)
			{
				++$count;
			}
		}

		if ($count > static::$sessionsPerType[$type])
		{
			foreach ($this->sessions as $session)
			{
				if ($session->type == $type)
				{
					--$count;
					$this->removeSession($session);
					if ($count <= static::$sessionsPerType[$type])
					{
						break;
					}
				}
			}
		}
	}

	public function getSessionByType($type)
	{

		$typeSessions = array();

		foreach($this->sessions as $session)
		{
			if($session->type == $type)
			{
				return $session;
			}
		}

	}
        
}