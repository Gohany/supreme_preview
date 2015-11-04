<?php
require_once $_SERVER['_HTDOCS_'] . '/libs/oauth/include.php';
interface socialMediaOAuthInterface
{

	public function getService();

	public function getAuthUrl();

	public function getAccessToken($code);

	public function setAccessToken($token);
}

interface socialMediaInterface
{

	public static function loadRequestInformation($requestIdentifier);

	public function saveRequestInformation();
}

abstract class socialMedia implements socialMediaInterface
{
	##
	# Constants
	##
	const REQUEST_CACHE_TIME = 900;

	const TYPE_TWITTER = 'twitter';
	const TYPE_FACEBOOK = 'facebook';
	const TYPE_GOOGLE = 'google';
	const TYPE_MOBILE_APP = 'mobileapp';

	##
	# Properties
	##
	protected $email;
	protected $nickname;
	protected $betaKey;
	protected $requestIdentifier;
	protected $clientCode;
	protected $remote_id;

	##
	# OAuth Classes
	##
	protected $accessToken;
	protected $storage;
	protected $service;

	public function __construct($email, $nickname, $remote_id = null)
	{
		$this->email = $email;
		$this->nickname = $nickname;
		$this->remote_id = $remote_id;
	}

	public function getNickname()
	{
		return $this->nickname;
	}

	public function getBetaKey()
	{
		return $this->betaKey;
	}

	public function getEmail()
	{
		return $this->email;
	}

	public function getRemoteId()
	{
		return $this->remote_id;
	}

	public function saveRequestInformation()
	{
		if (empty($this->requestIdentifier))
		{
			$this->requestIdentifier = self::createRequestIdentifier();
		}

		$cacheEntry = new cacheEntry(self::getCacheKeyName($this->requestIdentifier));
		$cacheEntry->value = serialize($this);
		$cacheEntry->expiration = self::REQUEST_CACHE_TIME;
		$cacheEntry->set();
	}

	/**
	 *
	 * @param string $requestIdentifier
	 * @return socialMedia
	 * @throws error
	 */
	public static function loadRequestInformation($requestIdentifier)
	{
		$cacheEntry = new cacheEntry(self::getCacheKeyName($requestIdentifier));
		if (!$cacheEntry->get())
		{
			throw new error(errorCodes::ERROR_REQUEST_TIMED_OUT);
		}

		return unserialize($cacheEntry->value);
	}

	public function getClientCode()
	{
		if (is_null($this->clientCode))
		{
			$this->clientCode = bin2hex(utility::getRandomBytes(4));
		}

		return $this->clientCode;
	}

	/**
	 *
	 * @return \OAuth\ServiceFactory
	 */
	protected function getServiceFactory()
	{
		$serviceFactory = new OAuth\ServiceFactory();
		$serviceFactory->setHttpClient(new \OAuth\Common\Http\Client\CurlClient());
		return $serviceFactory;
	}

	/**
	 *
	 * @param type $key
	 * @param type $secret
	 * @return \OAuth\Common\Consumer\Credentials
	 */
	protected function getCredentials($key, $secret)
	{
		$uriFactory = new OAuth\Common\Http\Uri\UriFactory();
		$currentUri = $uriFactory->createFromSuperGlobalArray($_SERVER);
		$currentUri->setQuery('');
		$currentUri->setHost('');
		$currentUri->setPath('/oauth/' . static::getSocialMediaType() . '.php');

		return new OAuth\Common\Consumer\Credentials($key, $secret, $currentUri->getAbsoluteUri());
	}

	protected static function getSocialMediaType()
	{
		return str_replace('socialmedia', '', strtolower(get_called_class()));
	}

	protected static function createRequestIdentifier()
	{
		return bin2hex(utility::getRandomBytes(16));
	}

	protected static function getCacheKeyName($requestIdentifier)
	{
		return 'SocialMedia_' . self::getSocialMediaType() . '_' . $requestIdentifier;
	}
}