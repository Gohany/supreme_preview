<?php

/**
 * @property OAuth\OAuth2\Service\Facebook $service
 */
class socialMediaFacebook extends socialMedia implements socialMediaOAuthInterface
{
	const FACEBOOK_CLIENT_ID = '311710578967105';
	const FACEBOOK_CLIENT_SECRET = '4a5ba1f93df68a2dc8724ac8e7391d12';

	/**
	 *
	 * @param array $scope
	 * @return OAuth\OAuth2\Service\Facebook
	 */
	public function getService(array $scopes = array())
	{
		if (!is_null($this->service))
		{
			return $this->service;
		}

		$this->storage = new OAuth\Common\Storage\Memory();
		$credentials = $this->getCredentials(self::FACEBOOK_CLIENT_ID, self::FACEBOOK_CLIENT_SECRET);
		$serviceFactory = $this->getServiceFactory();

		$this->service = $serviceFactory->createService('facebook', $credentials, $this->storage, $scopes);

		return $this->service;
	}

	public function getAuthUrl()
	{
		$this->saveRequestInformation();

		$service = $this->getService();
		$url = $service->getAuthorizationUri(array('state' => $this->requestIdentifier));

		return $url->getAbsoluteUri();
	}

	/**
	 *
	 * @param string|null $code
	 * @return OAuth\OAuth2\Token\StdOAuth2Token
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
			$this->accessToken = $this->getService()->requestAccessToken($code);
		}
		catch (Exception $e)
		{
			error::addError($e->getMessage());
			throw new error(errorCodes::ERROR_REQUEST_TIMED_OUT);
		}

		return $this->accessToken;
	}

	public function getAccountInfo()
	{
		try
		{
			return json_decode($this->getService()->request('/me'), true);
		}
		catch (Exception $ex)
		{
			return false;
		}
	}

	/**
	 *
	 * @param \OAuth\OAuth2\Token\StdOAuth2Token $token
	 */
	public function setAccessToken($token)
	{
		$this->accessToken = $token;
	}
}