<?php
//Unirest
require_once $_SERVER['_HTDOCS_'] . '/libs/unirest/HttpMethod.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/unirest/HttpResponse.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/unirest/Unirest.php';

//Sendgrid
require_once $_SERVER['_HTDOCS_'] . '/libs/sendgrid/SendGrid/Api.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/sendgrid/SendGrid/AuthException.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/sendgrid/SendGrid/EmailInterface.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/sendgrid/SendGrid/Email.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/sendgrid/SendGrid/SmtpapiHeaders.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/sendgrid/SendGrid/Web.php';

class SendGrid
{
	const VERSION = "1.1.5";

	protected $username;
	protected $password;
	protected $web;
	protected $smtp;

	public function __construct($username, $password)
	{
		$this->username = $username;
		$this->password = $password;
	}

	public static function getInstance()
	{
		$path = $_SERVER['_HTDOCS_'] . '/configs/smtp.php';
		if (!is_file($path))
		{
			error::addError('Missing mail configuration.');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}
		require $path;

		if (!isset($username, $password))
		{
			error::addError('Bad mail configuration; missing username or password.');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		return new SendGrid($username, $password);
	}

	public function __get($api)
	{
		$name = $api;

		if ($this->$name != null)
		{
			return $this->$name;
		}

		$api = "SendGrid\\" . ucwords($api);
		if (!class_exists($api))
		{
			throw new Exception("Api '$api' not found.");
		}

		$this->$name = new $api($this->username, $this->password);
		return $this->$name;
	}
}