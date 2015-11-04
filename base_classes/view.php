<?php
class view
{

	public static function getOutput($input)
	{
		switch (gettype($input))
		{
			case 'object':
				$arrayInput = get_object_vars($input);
				//Filter our input based on the view file
				$input = array_intersect_key($arrayInput, self::getViewProperties($input));
			case 'array':
				$output = array();
				foreach ($input as $key => $value)
				{
					$result = self::getOutput($value);
					if ($result !== null)
					{
						$output[$key] = $result;
					}
				}
				return $output;

			default:
				return $input;
		}
	}

	private static function getViewProperties($class)
	{
                
		/* @var $clientInfo clientInfo */
		if (!$clientInfo = dataStore::getObject('clientInfo'))
		{
                        print "returning empty array";
			return array();
		}

		$viewOptions = (!empty($class->viewOptions)) ? ('.' . implode('.', $class->viewOptions)) : '';

		$className = get_class($class);
		$environment = model::getEnvironmentFromClassName($className);
                
		$path = $_SERVER['_HTDOCS_'] . '/environments/' . $environment . '/views/' . $className;
                
		if ($clientInfo->isAuthed() && is_readable($path . '/common' . $viewOptions . '.php'))
		{
			require $path . '/common' . $viewOptions . '.php';
		}

		$propertiesToKeep = isset($array) ? $array : array();

		//If authed, use null for autodetect, else 'none'
		$clientType = ($clientInfo->isAuthed()) ? null : clientInfo::CLIENTTYPE_UNKNOWN_CLIENT;

		$fileName = $path . '/' . $clientInfo->getClientName($clientType) . $viewOptions . '.php';
		if (is_file($fileName))
		{
			require $fileName;
			$propertiesToKeep = array_merge($propertiesToKeep, $array);
		}

		return array_flip($propertiesToKeep);
	}
}