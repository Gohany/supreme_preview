<?php
require_once $_SERVER['_HTDOCS_'] . '/base_include.php';

$environment = (isset($_GET['environment']) ? filter_var($_GET['environment'], FILTER_SANITIZE_STRING, array(FILTER_FLAG_STRIP_LOW, FILTER_FLAG_STRIP_HIGH)) : 'base');
?>
<form action="" method="GET" id="environmentChanger">
	<select name="environment" onChange="document.getElementById('environmentChanger').submit();">
		<?PHP
		//Load base permission definitions
		foreach (glob($_SERVER['_HTDOCS_'] . '/environments/*') as $environmentPath)
		{
			$explode = explode('/', $environmentPath);
			if (is_file($_SERVER['_HTDOCS_'] . '/environments/' . $explode[count($explode) - 1] . '/permissions/' . $explode[count($explode) - 1] . 'Permissions.php'))
			{
				require_once $_SERVER['_HTDOCS_'] . '/environments/' . $explode[count($explode) - 1] . '/permissions/' . $explode[count($explode) - 1] . 'Permissions.php';
			}
			?>
			<option<?= ($explode[count($explode) - 1] == $environment) ? ' selected="selected"' : ''; ?>><?= $explode[count($explode) - 1]; ?></option>
			<?PHP
		}
		?>
	</select>
</form>
<?PHP
//Parse controller permissions
echo '<pre>Environment: <u>' . $environment . "</u>" . PHP_EOL . PHP_EOL;

foreach (glob($_SERVER['_HTDOCS_'] . '/environments/' . $environment . '/controllers/*.php') as $controllerPath)
{
	$explode = explode('/', $controllerPath);
	$controllerName = substr($explode[count($explode) - 1], 0, -4);

	echo "<u>" . $controllerName . "</u>" . PHP_EOL;

	require_once $controllerPath;

	$permissions = array();
	if (!is_file($_SERVER['_HTDOCS_'] . '/environments/' . $environment . '/permissions/' . $controllerName . 'Permissions.php'))
	{
		echo "\t<strong>Missing Permissions File</strong>" . PHP_EOL . PHP_EOL;
		continue;
	}
	require_once $_SERVER['_HTDOCS_'] . '/environments/' . $environment . '/permissions/' . $controllerName . 'Permissions.php';

	$reflection = new ReflectionClass($controllerName);
	foreach (array('display', 'create', 'modify', 'delete') as $actionType)
	{
		if (!$reflection->hasMethod($actionType))
		{
			continue;
		}

		echo "\tAvailable Action: " . $actionType . PHP_EOL;

		if (!isset($permissions[$actionType]))
		{
			echo "\t\t<strong>Missing Permission Settings</strong>" . PHP_EOL . PHP_EOL;
			continue;
		}

		if (empty($permissions[$actionType]))
		{
			echo "\t\tNo permissions required" . PHP_EOL . PHP_EOL;
			continue;
		}

		foreach (array('a' => 'Admin', 'c' => 'Client', 'cs' => 'Chat Server', 's' => 'Game Server') as $clientType => $clientName)
		{
			if (!isset($permissions[$actionType][$clientType]))
			{
				continue;
			}
			echo "\t\t<u>" . $clientName . "</u>" . PHP_EOL;
			if ($actionType == 'modify' || ($actionType == 'create' && $controllerName == 'betaKey'))
			{
				foreach ($permissions[$actionType][$clientType] as $modifyAction => $p)
				{
					echo "\t\t\t<u>" . $modifyAction . "</u>" . PHP_EOL;
					if (isset($permissions[$actionType][$clientType][$modifyAction]['validation']))
					{
						echo "\t\t\t\tRequires Valid " . $clientName . " Session" . PHP_EOL;
					}
					if (isset($permissions[$actionType][$clientType][$modifyAction]['ownership']))
					{
						echo "\t\t\t\tRequires Ownership" . PHP_EOL;
					}
					echo PHP_EOL;
				}
			}
			else
			{
				if (isset($permissions[$actionType][$clientType]['validation']))
				{
					echo "\t\t\tRequires Valid " . $clientName . " Session" . PHP_EOL;
				}
				if (isset($permissions[$actionType][$clientType]['ownership']))
				{
					echo "\t\t\tRequires Ownership" . PHP_EOL;
				}
				echo PHP_EOL;
			}
		}
	}
}
echo '</pre>';