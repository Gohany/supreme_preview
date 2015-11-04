<?php

/**
 * @property OAuth\OAuth1\Service\Twitter $service
 */
class socialMediaTwitter extends socialMedia implements socialMediaOAuthInterface
{
	const TWITTER_CLIENT_KEY = 'FibSkscxbKVzRzRODdE9g';
	const TWITTER_CLIENT_SECRET = 'CUgX70KRKyt2w3UV4ND4eYfWwJUnzygxJXRrlHsw';

	/**
	 *
	 * @return OAuth\OAuth1\Service\Twitter
	 */
	public function getService()
	{
		if (!is_null($this->service))
		{
			return $this->service;
		}

		$this->storage = new OAuth\Common\Storage\Memory();
		$credentials = $this->getCredentials(self::TWITTER_CLIENT_KEY, self::TWITTER_CLIENT_SECRET);
		$serviceFactory = $this->getServiceFactory();

		$this->service = $serviceFactory->createService('twitter', $credentials, $this->storage);
		$this->saveRequestInformation();

		return $this->service;
	}

	/**
	 * Get URL to auth this service with.
	 * @return string
	 */
	public function getAuthUrl()
	{
		$token = $this->getService()->requestRequestToken();
		$this->requestIdentifier = $token->getRequestToken();

		$url = $this->getService()->getAuthorizationUri(array('oauth_token' => $token->getRequestToken()));

		$this->saveRequestInformation();

		return $url->getAbsoluteUri();
	}

	/**
	 *
	 * @param type $code
	 * @return \OAuth\OAuth1\Token\StdOAuth1Token
	 * @throws error
	 */
	public function getAccessToken($code = null)
	{
		if (!is_null($this->accessToken))
		{
			return $this->accessToken;
		}

		try
		{
			$this->accessToken = $this->getService()->requestAccessToken($this->requestIdentifier, $code);
		}
		catch (Exception $e)
		{
			error::addError($e->getMessage());
			throw new error(errorCodes::ERROR_REQUEST_TIMED_OUT);
		}

		$this->saveRequestInformation();

		return $this->accessToken;
	}

	/**
	 *
	 * @param \OAuth\OAuth1\Token\StdOAuth1Token $token
	 */
	public function setAccessToken($token)
	{
		$this->accessToken = $token;
	}

	public function getAccountInfo()
	{
		try
		{
			$service = $this->getService();
			$service->getStorage()->storeAccessToken('twitter', $this->getAccessToken());

			return json_decode($service->request('account/verify_credentials.json'));
		}
		catch (Exception $e)
		{
			return false;
		}
	}

	public static function createAccessToken($accessTokenKey, $accessTokenSecretKey)
	{
		$token = new OAuth\OAuth1\Token\StdOAuth1Token();
		$token->setAccessToken($accessTokenKey);
		$token->setAccessTokenSecret($accessTokenSecretKey);

		return $token;
	}
}