<?PHP
require_once $_SERVER['_HTDOCS_'] . '/base_include.php';

print "<pre>";
$controllers = array();
if (!isset($_GET['output']))
{
	$_GET['output'] = 'html';
}

$controller_reflection = new ReflectionClass('Controller');
$environments = array('base');

if (isset($_GET['environment']) && in_array($_GET['environment'], $environments))
{
	$environment = $_GET['environment'];
}
else
{
	$environment = 'base';
}

if ($_GET['output'] != 'serialized')
{
	print "Environment: " . $environment . PHP_EOL;
}

foreach (glob($_SERVER['_HTDOCS_'] . '/environments/' . $environment . '/controllers/*.php') as $filename)
{
	require_once $filename;
	$className = basename($filename, '.php');

	if ($_GET['output'] == 'serialized')
	{
		$controllers[$className] = array();
	}
	else
	{
		print "<br />Available controller: " . $className . "<br />" . PHP_EOL;
	}

	$reflection = new ReflectionClass($className);
	$primary_model = $reflection->getConstant('PRIMARY_MODEL');
	$methods = $reflection->getMethods();

	foreach ($methods as $methodObjects)
	{
		if (!$controller_reflection->hasMethod($methodObjects->name))
		{
			if ($_GET['output'] == 'serialized')
			{
				$controllers[$className]['Methods'][$methodObjects->name] = array();
			}
			else
			{
				print "\tAvailable method: " . $methodObjects->name . PHP_EOL;
			}
		}
	}

	if (is_file($_SERVER['_HTDOCS_'] . '/environments/' . $environment . '/models/' . substr($primary_model, 0, strlen($primary_model) - 5) . '.php'))
	{
		require_once $_SERVER['_HTDOCS_'] . '/environments/' . $environment . '/models/' . substr($primary_model, 0, strlen($primary_model) - 5) . '.php';
		$primary_model_reflection = new ReflectionClass($primary_model);
		$primary_model_methods = $primary_model_reflection->getMethods();
		if ($_GET['output'] == 'serialized')
		{
			$controllers[$className]['Methods']['display'] = array();
		}
		else
		{
			print "\tAvailable method: display" . PHP_EOL;
		}
		foreach ($primary_model_methods as $methodObjects)
		{
			if ($primary_model_reflection->hasProperty($methodObjects->name) && $primary_model_reflection->hasMethod($methodObjects->name) && !method_exists('model', $methodObjects->name))
			{
				$property = $primary_model_reflection->getProperty($methodObjects->name);
				if ($property->isPublic())
				{
					if ($_GET['output'] == 'serialized')
					{
						$controllers[$className]['Methods'][$primary_model]['options'][] = $methodObjects->name;
					}
					else
					{
						print "\t\tAvailable option:\t" . $methodObjects->name . PHP_EOL;
					}
				}
			}
		}
	}

#$controllers[$className] = $reflection;
}
if ($_GET['output'] != 'serialized')
{
	print "</pre>";
}
else
{
	print serialize($controllers);
}