<?php

class clientInfo
{
	const CLIENTTYPE_UNKNOWN_CLIENT = '';
	const CLIENTTYPE_CLIENT = 'c';
	const CLIENTTYPE_ADMIN = 'a';
        const CLIENTTYPE_ACCOUNT = 'ac';
        const CLIENTTYPE_SERVER = 's';

	protected $id;
	protected $clientType;
	protected $environment;
	protected $signature;
	protected $isAuthed = false;
        protected $dbKeys = array();

	public function __construct($clientType = self::CLIENTTYPE_UNKNOWN_CLIENT, $id = null, $environment = null, $signature = null)
	{
		$this->id = $id;
		$this->environment = $environment;
		$this->clientType = $clientType;
		$this->signature = $signature;
	}
        
        public function setDBKey($key, $value)
        {
                $this->dbKeys[$key] = $value;
        }
        
        public function getDBKey($key)
        {
                if (in_array($key, $this->dbKeys))
                {
                        return $this->dbKeys[$key];
                }
                return false;
        }
        
	/**
	 *
	 * @return boolean
	 */
	public function isAuthed()
	{
		return $this->isAuthed;
	}

	/**
	 *
	 * @param boolean $authed
	 */
	public function setAuthed($authed = true)
	{
		$this->isAuthed = (bool) $authed;
	}

	/**
	 * Client's current ID, be it: chat, client, game, or admin.
	 * @return float
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Client's session signature
	 * @return string
	 */
	public function getSignature()
	{
		return $this->signature;
	}

	/**
	 * Returns 1-2 string representation of clientType (client returns 'c')
	 *
	 * @return string
	 */
	public function getClientType()
	{
		return $this->clientType;
	}
	public function getEnvironment()
	{
		return $this->environment;
	}

	/**
	 * Returns full string for clientType (client returns 'client')
	 *
	 * @return string
	 */
	public function getClientName($clientType = null)
	{
		if ($clientType === null)
		{
			$clientType = self::getClientType();
		}

		if (!isset(dispatcher::$requesters[$clientType]))
		{
			return dispatcher::$requesters[self::CLIENTTYPE_UNKNOWN_CLIENT];
		}

		return dispatcher::$requesters[$clientType];
	}

	/**
	 *
	 * @return boolean
	 */
	public function isUnknownClient()
	{
		return ($this->clientType === self::CLIENTTYPE_UNKNOWN_CLIENT);
	}

	/**
	 *
	 * @return boolean
	 */
	public function isClient()
	{
		return ($this->clientType === self::CLIENTTYPE_CLIENT);
	}

	/**
	 *
	 * @return boolean
	 */
	public function isAdmin()
	{
		return ($this->clientType === self::CLIENTTYPE_ADMIN);
	}

}