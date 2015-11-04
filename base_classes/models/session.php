<?PHP

class baseSession extends model
{
        
        public $sessionKey;
	public $type;
	public $clientData;
	public $expiration;
        public $sessionData;
        
	public function validChallenge($challenge, $hash)
	{
		if (utility::hmac($challenge, $this->sessionKey) !== $hash || $this->sessionKey === null)
		{
			error::addError("Challenge failed.");
			throw new error(errorCodes::ERROR_CHALLENGE_BAD);
		}
		return true;
	}

	public function validSessionKey($sessionKey)
	{
		if ($this->sessionKey !== $sessionKey || $this->sessionKey === null)
		{
			error::addError('Session key mismatch.');
			throw new error(errorCodes::ERROR_SESSION_INVALID);
		}
		return true;
	}
        
        public function isExpired()
	{
		if ($this->expiration === null)
		{
			return false;
		}

		return strtotime($this->expiration) < time();
	}
        
        public function signatureIteration($authToken)
        {
                
                if (empty($this->sessionData) || !is_array($this->sessionData) || !isset($this->sessionData['validatedRequests']))
                {
                        $this->sessionData['validatedRequests'] = 1;
                }
                
                return utility::hash($this->signature($authToken) . $this->sessionData['validatedRequests'] . static::ITERATION_SALT);
                
        }
        
        public function signature($authToken)
        {
                return utility::hmac($this->sessionKey, $authToken);
        }
        
}