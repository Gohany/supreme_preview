<?php
class request
{
	const LOG_KEY = 'RequestLog';

	private $startTime;
	private $endTime;
	private $method;
	private $redirectUrl;
	private $requester;
	private $controller;
	private $environment;
	private $input;
	private $authentication;
	private $uniqueId;

	public function __construct($startTime = null)
	{
		if ($startTime === null)
		{
			$startTime = microtime(true);
		}

		$this->uniqueId = dechex(mt_rand(1, mt_getrandmax()));

		$this->startRequest($startTime);
	}

	protected function startRequest($startTime)
	{
		$this->startTime = $startTime;

		if (isset($_SERVER['REQUEST_METHOD']))
		{
			$this->method = $_SERVER['REQUEST_METHOD'];
		}

		if (isset($_SERVER['REDIRECT_URL']))
		{
			$this->redirectUrl = trim($_SERVER['REDIRECT_URL'], '/');

			$explode = explode('/', $this->redirectUrl);
			if (count($explode) >= 3)
			{
				$this->requester = $explode[0];
				$this->environment = $explode[1];
				$this->controller = $explode[2];
			}
		}

		$this->input = $_REQUEST;

		if (isset($_SERVER[dispatcher::AUTHENTICATION_HEADER]))
		{
			$this->authentication = $_SERVER[dispatcher::AUTHENTICATION_HEADER];
		}
	}

	public function endRequest()
	{
		$this->endTime = microtime(true);
	}

	public function isRequestFinished()
	{
		return (!is_null($this->endTime));
	}

	public function getRequestStartTime()
	{
		return $this->startTime;
	}

	public function getRequestEndTime()
	{
		return $this->endTime;
	}

	public function getTotalRequestTime()
	{
		if (!$this->isRequestFinished())
		{
			return false;
		}

		return $this->getRequestEndTime() - $this->getRequestStartTime();
	}

	public function getRequestData()
	{
		return array(
			'authentication' => $this->authentication,
			'input' => $this->input,
			'method' => $this->method,
			'redirectUrl' => $this->redirectUrl,
			'requester' => $this->requester,
			'controller' => $this->controller,
			'environment' => $this->environment,
			'startTime' => $this->startTime,
			'uniqueId' => $this->uniqueId
		);
	}

	public function log()
	{
		$time = ($this->isRequestFinished()) ? $this->getRequestEndTime() : $this->getRequestStartTime();

		$serializedRedisData = serialize($this->getRequestData());

		try
		{
			$redis = RedisPool::getRedisKey(self::LOG_KEY);
			$redis->zAdd($time, $serializedRedisData);

			return $serializedRedisData;
		}
		catch (Exception $e)
		{
			error::clearErrors();
			return false;
		}
	}
}