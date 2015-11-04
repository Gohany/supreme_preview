<?php
class asyncTracking
{
	public $functions = array();
	public $functionData = array();
	public $id;
	public $environment;
	public static $instance;
	public static $availableFunctions = array(
                'functionName',
	);

	public function __construct($id, $environment)
	{
		$this->id = $id;
		$this->environment = $environment;
	}

	public static function hasAsyncData()
	{
		return (bool) count(self::singleton()->functionData);
	}

	public static function singleton()
	{
		if (is_null(self::$instance))
		{
			self::$instance = self::fromClientInfo();
		}
		return self::$instance;
	}

	public static function fromClientInfo()
	{
		$clientInfo = dataStore::getObject('clientInfo');
		return new asyncTracking($clientInfo->getId(), $clientInfo->getEnvironment());
	}

	public static function addFunction($function, $data = null)
	{
		if (!in_array($function, self::$availableFunctions))
		{
			return false;
		}
		if (self::isFunctionSet($function) === false)
		{
			self::singleton()->functions[] = $function;
		}
		if (!empty($data))
		{
			self::singleton()->addFunctionData($function, $data);
		}
		return true;
	}

	public static function removeFunction($function)
	{
		$key = self::isFunctionSet($function);
		if ($key !== false)
		{
			unset(self::singleton()->functions[$key]);
		}
	}

	public static function isFunctionSet($function)
	{
		return array_search($function, self::singleton()->functions);
	}

	public static function addFunctionData($function, $data)
	{
		if (!in_array($function, self::singleton()->functions))
		{
			return false;
		}
		if (empty(self::singleton()->functionData[$function]))
		{
			self::singleton()->functionData[$function] = $data;
		}
		else
		{
			self::singleton()->functionData[$function] = array_merge(self::singleton()->functionData[$function], $data);
		}
	}

	public static function getFunctionData()
	{
		if (count(self::single()->functionData) == 0)
		{
			return false;
		}
		return self::singleton()->functionData;
	}

	public static function runGearman()
	{
		if (!datastore::getObject('clientInfo'))
		{
			return;
		}

		if (!self::hasAsyncData())
		{
			return;
		}

		$data = self::singleton()->functionData;

		$gmclient = new GearmanClient();
		if ($gmclient->addServer('127.0.0.1', 4730))
		{
			foreach ($data as $function => $trackingData)
			{
				$gmclient->doBackground($function, serialize($trackingData));
			}
		}
	}
}