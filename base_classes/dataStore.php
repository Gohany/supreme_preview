<?PHP

class dataStore
{
	public static $instance;
	private $objects = array();
	private $resources = array();
	public $strings = array();
	private $arrays = array();

	const DEFAULT_OBJECT_TYPE = 'public';
	const DEFAULT_STRING_TYPE = 'public';
	const DEFAULT_ARRAY_TYPE = 'public';

	public static function singleton()
	{
		self::$instance || self::$instance = new dataStore();

		return self::$instance;
	}

	public static function setObject($key, $object, $type = self::DEFAULT_OBJECT_TYPE)
	{
		self::singleton()->objects[$type][$key] = $object;
	}

	public static function setString($key, $string, $type = self::DEFAULT_STRING_TYPE)
	{
		self::singleton()->strings[$type][$key] = $string;
	}

	public static function appendString($key, $string, $type = self::DEFAULT_STRING_TYPE)
	{
		if (isset(self::singleton()->strings[$type][$key]))
		{
			self::singleton()->strings[$type][$key] .= $string;
		}
		else
		{
			self::singleton()->strings[$type][$key] = $string;
		}
	}

	public static function unsetStringType($type)
	{
		unset(self::singleton()->strings[$type]);
	}

	public static function getString($key, $type = self::DEFAULT_STRING_TYPE)
	{
		return isset(self::singleton()->strings[$type][$key]) ? self::singleton()->strings[$type][$key] : false;
	}

	public static function getStringArray($type = self::DEFAULT_STRING_TYPE)
	{
		return isset(self::singleton()->strings[$type]) ? self::singleton()->strings[$type] : false;
	}

	public static function setArray($key, $array, $type = self::DEFAULT_ARRAY_TYPE)
	{
		self::singleton()->arrays[$type][$key] = $array;
	}

	public static function mergeArray($key, $array, $type = self::DEFAULT_ARRAY_TYPE)
	{
		if (!isset(self::singleton()->arrays[$type][$key]))
		{
			self::singleton()->arrays[$type][$key] = $array;
		}
		else
		{
			self::singleton()->arrays[$type][$key] = array_merge(self::singleton()->arrays[$type][$key], $array);
		}
	}

	public static function appendArray($key, $array, $type = self::DEFAULT_ARRAY_TYPE)
	{
		self::singleton()->arrays[$type][$key][] = $array;
	}

	public static function appendArrayByKey($key, $key2, $array, $type = self::DEFAULT_ARRAY_TYPE)
	{
		self::singleton()->arrays[$type][$key][$key2] = $array;
	}

	public static function unsetArrayType($type)
	{
		unset(self::singleton()->arrays[$type]);
	}

	public static function getArray($key, $type = self::DEFAULT_ARRAY_TYPE)
	{
		return isset(self::singleton()->arrays[$type][$key]) ? self::singleton()->arrays[$type][$key] : false;
	}

	public static function getArrayArray($type = self::DEFAULT_ARRAY_TYPE)
	{
		return isset(self::singleton()->arrays[$type]) ? self::singleton()->arrays[$type] : false;
	}

	public static function unsetObjectType($type)
	{
		unset(self::singleton()->objects[$type]);
	}

	public static function setResource($key, $resource)
	{
		if (is_resource($resource))
		{
			if ($type = get_resource_type($resource))
			{
				self::singleton()->resources[$type][$key] = $resource;
			}
		}
	}

	public static function getObject($key, $type = self::DEFAULT_OBJECT_TYPE)
	{
		return isset(self::singleton()->objects[$type][$key]) ? self::singleton()->objects[$type][$key] : false;
	}

	public static function getResource($key, $type)
	{
		return isset(self::singleton()->resources[$type][$key]) ? self::singleton()->resources[$type][$key] : false;
	}

	public static function getObjectArray($type = self::DEFAULT_OBJECT_TYPE)
	{
		return isset(self::singleton()->objects[$type]) ? self::singleton()->objects[$type] : false;
	}

	public static function getResourceArray($type)
	{
		return isset(self::singleton()->resources[$type]) ? self::singleton()->resources[$type] : false;
	}
}
