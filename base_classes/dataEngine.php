<?php

class dataEngine
{

	public static function read($key, array $array = array())
	{
		self::includeDataclass($key);

		$object_name = $key . '_dataClass';
		if (!method_exists($object_name, __FUNCTION__))
		{
			error::addError('Method not defined in data object.');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		$object = new $object_name();
		return $object->read($key, $array);
	}

	public static function dump($key, array $array = array())
	{
		self::includeDataclass($key);

		$object_name = $key . '_dataClass';
		if (!method_exists($object_name, __FUNCTION__))
		{
			error::addError('Method not defined in data object.');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		$object = new $object_name();
		return $object->dump($key, $array);
	}

	public static function truncate($key, array $array = array())
	{
		self::includeDataclass($key);

		$object_name = $key . '_dataClass';
		if (!method_exists($object_name, __FUNCTION__))
		{
			error::addError('Method not defined in data object.');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		$object = new $object_name();
		return $object->truncate($key, $array);
	}

	public static function write($key, array $array, $engine = false)
	{
		self::includeDataclass($key);

		$object_name = $key . '_dataClass';
		if (!method_exists($object_name, __FUNCTION__))
		{
			error::addError('Method not defined in data object.');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		$object = new $object_name();
		return $object->write($key, $array, $engine);
	}

	private static function includeDataclass($key)
	{
//		if (!defined('dataClass_' . $key))
//		{
//			error::addError('Data object ' . $key . ' not defined.');
//			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
//		}
//
//		if (!constant('dataClass_' . $key))
//		{
//			error::addError('Data object is not available.');
//			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
//		}

		$fileList = glob($_SERVER['_HTDOCS_'] . '/data_classes/{,*/}' . $key . '.php', GLOB_NOSORT | GLOB_BRACE);
		if (!$fileList)
		{
			error::addError('Missing dataclass "' . $key . '".');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		foreach ($fileList as $file)
		{
			require_once $file;
		}
	}
}
