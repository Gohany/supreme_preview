<?php

class dataClass
{
	public static $write_methods = array('mysql');

	public function read($key, $array)
	{
		if (defined($key . '_read_method'))
		{
			if (method_exists($this, 'read_' . constant($key . '_read_method')))
			{
				return call_user_func(array($this, 'read_' . constant($key . '_read_method')), $array);
			}
		}
		elseif (defined('read_method'))
		{
			if (method_exists($this, 'read_' . read_method))
			{
				return call_user_func(array($this, 'read_' . read_method), $array);
			}
		}

		error::addError('No read method specified.');
		throw new error(errorCodes::ERROR_INTERNAL_ERROR);
	}

	public function dump($key, $array)
	{
		if (defined($key . '_dump_method'))
		{
			if (method_exists($this, 'dump_' . constant($key . '_dump_method')))
			{
				return call_user_func(array($this, 'dump_' . constant($key . '_dump_method')), $array);
			}
		}
		elseif (defined('dump_method'))
		{
			if (method_exists($this, 'dump_' . dump_method))
			{
				return call_user_func(array($this, 'dump_' . dump_method), $array);
			}
		}

		error::addError('No dump method specified.');
		throw new error(errorCodes::ERROR_INTERNAL_ERROR);
	}

	public function truncate($key, $array)
	{
		if (defined($key . '_truncate_method'))
		{
			if (method_exists($this, 'truncate_' . constant($key . '_truncate_method')))
			{
				return call_user_func(array($this, 'truncate_' . constant($key . '_truncate_method')), $array);
			}
		}
		elseif (defined('truncate_method'))
		{
			if (method_exists($this, 'truncate_' . truncate_method))
			{
				return call_user_func(array($this, 'truncate_' . truncate_method), $array);
			}
		}

		error::addError('No truncate method specified.');
		throw new error(errorCodes::ERROR_INTERNAL_ERROR);
	}

	public function write($key, $array, $engine = false)
	{
		if ($engine !== false)
		{
			if (method_exists($key . '_dataClass', 'write_' . $engine))
			{
				return call_user_func(array($this, 'write_' . $engine), $array);
			}
		}
		elseif (defined($key . '_write_method'))
		{
			if (method_exists($this, 'write_' . constant($key . '_write_method')))
			{
				return call_user_func(array($this, 'write_' . constant($key . '_write_method')), $array);
			}
		}
		else
		{
			foreach (self::$write_methods as $method)
			{
				if (!method_exists($key . '_dataClass', 'write_' . $method))
				{
					continue;
				}

				if (defined($key . '_' . $method . '_write'))
				{
					if (constant($key . '_' . $method . '_write') === true)
					{
						return call_user_func(array($this, 'write_' . $method), $array);
					}
				}
				elseif (defined('write_' . $method))
				{
					if (constant('write_' . $method) === true)
					{
						return call_user_func(array($this, 'write_' . $method), $array);
					}
				}
			}
		}

		error::addError('No write method specified.');
		throw new error(errorCodes::ERROR_INTERNAL_ERROR);
	}
}