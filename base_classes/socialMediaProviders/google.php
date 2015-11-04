<?php

/**
 * @property OAuth\OAuth2\Service\Google $service
 */
class socialMediaGoogle extends socialMedia implements socialMediaOAuthInterface
{
	const GOOGLE_CLIENT_ID = '636245430665.apps.googleusercontent.com';
	const GOOGLE_CLIENT_SECRET = '8kGtZJO_6wkH9gFKOwnaSetM';

	/**
	 *
	 * @param array $scope
	 * @return OAuth\OAuth2\Service\Google
	 */
	public function getService(array $scope = array('userinfo_profile'))
	{
		if (!is_null($this->service))
		{
			return $this->service;
		}

		$this->storage = new OAuth\Common\Storage\Memory();
		$credentials = $this->getCredentials(self::GOOGLE_CLIENT_ID, self::GOOGLE_CLIENT_SECRET);
		$serviceFactory = $this->getServiceFactory();

		/* @var $this->service OAuth\OAuth2\Service\Google */
		$this->service = $serviceFactory->createService('google', $credentials, $this->storage, $scope);

		return $this->service;
	}

	public function getAuthUrl()
	{
		$this->saveRequestInformation();

		$service = $this->getService();
		$url = $service->getAuthorizationUri(array('state' => $this->requestIdentifier, 'access_type' => 'offline'));

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
			return json_decode($this->getService()->request('https://www.googleapis.com/oauth2/v1/userinfo'), true);
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