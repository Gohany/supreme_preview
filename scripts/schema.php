<?PHP
set_time_limit(0);
error_reporting(E_ALL);
require_once $_SERVER['_HTDOCS_'] . '/base_include.php';
require_once $_SERVER["_HTDOCS_"] . "/configs/config.php";

$output = array();
$schema = array();
$schemaWarning = false;

$fastMode = (isset($_GET['fast']) && $_GET['fast']);

$shardList = shard::shardDump();
ksort($shardList);

echo "<pre>";
foreach ($shardList as $shardName => $shardKeyList)
{
	$tableList = array();
	foreach ($shardKeyList as $shardKey => $shardDBLocation)
	{
		$databaseName = $shardName . "_" . $shardKey;

		// Tables //
		$mysqliTableList = mysql::query($shardDBLocation["key"], "SHOW TABLES IN `$databaseName`;");
		if ($mysqliTableList === false)
		{
			$schemaWarning = true;
			echo "<strong>Warning! Missing shard '" . $databaseName . "'</strong>" . PHP_EOL;
			continue;
		}

		if ($mysqliTableList->num_rows > 0)
		{
			for ($i = 0; $i < $mysqliTableList->num_rows; $i++)
			{
				$tableList = array_merge($tableList, $mysqliTableList->fetch_array(MYSQLI_NUM));
			}
		}
		$mysqliTableList->free();

		foreach ($tableList as $table)
		{
			$mysqliTableSchema = mysql::query($shardDBLocation["key"], "SHOW CREATE TABLE `$databaseName`.`$table`;");
			if ($mysqliTableSchema === false)
			{
				$error = 'Table is missing!';
				$schema[$shardName]['tables'][$table][$error][$databaseName] = $error;
				continue;
			}

			$result = $mysqliTableSchema->fetch_array(MYSQLI_NUM);
			$createQuery = str_replace("CREATE TABLE `$table`", "CREATE TABLE IF NOT EXISTS `|shard|`.`$table`", $result[1]);
			$createQuery = preg_replace("/AUTO_INCREMENT=\d+ /", "", $createQuery);
			$createQuery .= ";";
			$schema[$shardName]['tables'][$table][$createQuery][$databaseName] = $createQuery;
			$mysqliTableSchema->free();
		}

		// Triggers //
		$mysqliTriggerList = mysql::query($shardDBLocation["key"], "SHOW TRIGGERS IN `$databaseName`;");
		if (($mysqliTriggerList !== false) && ($mysqliTriggerList->num_rows > 0))
		{
			while ($mysqliTriggerRow = $mysqliTriggerList->fetch_array(MYSQLI_NUM))
			{
				$triggerName = $mysqliTriggerRow[0];
				$mysqliTriggerSchema = mysql::query($shardDBLocation["key"], "SHOW CREATE TRIGGER `$databaseName`.`$triggerName`;");
				if ($mysqliTriggerSchema !== false)
				{
					$result = $mysqliTriggerSchema->fetch_array(MYSQLI_NUM);
					$createQuery = $result[2];
					$createQuery = preg_replace("/DEFINER=`.+`\@`.+` /", "", $createQuery);
					$createQuery = str_replace('`' . $databaseName . '`', '`|shard|`', $createQuery);
					$createQuery = "DROP TRIGGER IF EXISTS `|shard|`.`$triggerName`; \nDELIMITER | \n" . $createQuery . " |\nDELIMITER ;\n";
					$schema[$shardName]['triggers'][$triggerName][$createQuery][$databaseName] = $createQuery;
					$mysqliTriggerSchema->free();
				}
			}
		}
		$mysqliTriggerList->free();

		// Procedures
		$mysqliProcedureList = mysql::query($shardDBLocation["key"], "SELECT `name` FROM `mysql`.`proc` WHERE `db` = '$databaseName';");
		if (($mysqliProcedureList !== false) && ($mysqliProcedureList->num_rows > 0))
		{
			while ($mysqliProcedureRow = $mysqliProcedureList->fetch_array(MYSQLI_NUM))
			{
				$procedureName = $mysqliProcedureRow[0];
				$mysqliProcedureSchema = mysql::query($shardDBLocation["key"], "SHOW CREATE PROCEDURE `$databaseName`.`$procedureName`;");
				if ($mysqliProcedureSchema !== false)
				{
					$result = $mysqliProcedureSchema->fetch_array(MYSQLI_NUM);
					$createQuery = $result[2];
					$createQuery = preg_replace("/DEFINER=`.+`\@`.+` /", "", $createQuery);
					$createQuery = str_replace("`$procedureName`()", "`|shard|`.`$procedureName`()", $createQuery);
					$createQuery = str_replace('`' . $databaseName . '`', '`|shard|`', $createQuery);
					$createQuery = str_replace("'" . $databaseName . "'", "'|shard|'", $createQuery);
					$createQuery = "DROP PROCEDURE IF EXISTS `|shard|`.`" . $procedureName . "`; \nDELIMITER | \n" . $createQuery . " |\nDELIMITER ;\n";
					$schema[$shardName]['procedures'][$procedureName][$createQuery][$databaseName] = $createQuery;
					$mysqliProcedureSchema->free();
				}
			}
		}
		$mysqliProcedureList->free();

		// Events
		$mysqliEventList = mysql::query($shardDBLocation["key"], "SHOW EVENTS IN `$databaseName`;");
		if (($mysqliEventList !== false) && ($mysqliEventList->num_rows > 0))
		{
			while ($mysqliEventRow = $mysqliEventList->fetch_array(MYSQLI_NUM))
			{
				$eventName = $mysqliEventRow[1];
				$mysqliEventSchema = mysql::query($shardDBLocation["key"], "SHOW CREATE EVENT `$databaseName`.`$eventName`;");
				if ($mysqliEventSchema !== false)
				{
					$result = $mysqliEventSchema->fetch_array(MYSQLI_NUM);
					$createQuery = $result[3];
					$createQuery = preg_replace("/DEFINER=`.+`\@`.+` /", "", $createQuery);
					$createQuery = str_replace("`$eventName`", "`|shard|`.`$eventName`", $createQuery);
					$createQuery = str_replace('`' . $databaseName . '`', '`|shard|`', $createQuery);
					$createQuery = str_replace("'" . $databaseName . "'", "'|shard|'", $createQuery);
					$createQuery = "DROP EVENT IF EXISTS `|shard|`.`" . $eventName . "`; \nDELIMITER | \n" . $createQuery . " |\nDELIMITER ;\n";
					$schema[$shardName]['events'][$eventName][$createQuery][$databaseName] = $createQuery;
					$mysqliEventSchema->free();
				}
			}
		}
		$mysqliEventList->free();

		if ($fastMode)
		{
			break;
		}
	}

	if (!$fastMode)
	{
		//Different Schema Check
		if (isset($schema[$shardName]['tables']))
		{
			foreach ($schema[$shardName]['tables'] as $key => $tableQuery)
			{
				if (count($tableQuery) > 1)
				{
					$schemaWarning = true;
					echo "<strong>Warning! Schema conflict amongst '" . $shardName . "' shards!</strong>" . PHP_EOL . PHP_EOL;
					foreach ($tableQuery as $query => $db)
					{
						echo "\t<strong>" . implode(", ", array_keys($db)) . "</strong> are the following:" . PHP_EOL . PHP_EOL;
						echo "\t\t" . str_replace("\n", "\n\t\t", $query) . PHP_EOL . PHP_EOL;
					}
					echo PHP_EOL;
				}
			}
		}

		if (isset($schema[$shardName]['triggers']))
		{
			foreach ($schema[$shardName]['triggers'] as $key => $triggerQuery)
			{
				if (count($triggerQuery) > 1)
				{
					$schemaWarning = true;
					echo "<strong>Warning! Schema conflict amongst '" . $shardName . "' shards!</strong>" . PHP_EOL . PHP_EOL;
					foreach ($triggerQuery as $query => $db)
					{
						echo "\t<strong>" . implode(", ", array_keys($db)) . "</strong> are the following:" . PHP_EOL . PHP_EOL;
						echo "\t\t" . str_replace("\n", "\n\t\t", $query) . PHP_EOL . PHP_EOL;
					}
					echo PHP_EOL;
				}
			}
		}

		if (isset($schema[$shardName]['procedures']))
		{
			foreach ($schema[$shardName]['procedures'] as $key => $procedureQuery)
			{
				if (count($procedureQuery) > 1)
				{
					$schemaWarning = true;
					echo "<strong>Warning! Schema conflict amongst '" . $shardName . "' shards!</strong>" . PHP_EOL . PHP_EOL;
					foreach ($procedureQuery as $query => $db)
					{
						echo "\t<strong>" . implode(", ", array_keys($db)) . "</strong> are the following:" . PHP_EOL . PHP_EOL;
						echo "\t\t" . str_replace("\n", "\n\t\t", $query) . PHP_EOL . PHP_EOL;
					}
					echo PHP_EOL;
				}
			}
		}

		if (isset($schema[$shardName]['events']))
		{
			foreach ($schema[$shardName]['events'] as $key => $eventQuery)
			{
				if (count($eventQuery) > 1)
				{
					$schemaWarning = true;
					echo "<strong>Warning! Schema conflict amongst '" . $shardName . "' shards!</strong>" . PHP_EOL . PHP_EOL;
					foreach ($eventQuery as $query => $db)
					{
						echo "\t<strong>" . implode(", ", array_keys($db)) . "</strong> are the following:" . PHP_EOL . PHP_EOL;
						echo "\t\t" . str_replace("\n", "\n\t\t", $query) . PHP_EOL . PHP_EOL;
					}
					echo PHP_EOL;
				}
			}
		}
	}

	$output[] = "<strong>## " . $shardName . ' ##</strong>';
	$output[] = "CREATE DATABASE IF NOT EXISTS `|shard|`;";
	if (!empty($schema[$shardName]['tables']))
	{
		foreach ($schema[$shardName]['tables'] as $tableQuery)
		{
			$output[] = key($tableQuery);
		}
		$output[] = PHP_EOL;
	}

	if (!empty($schema[$shardName]['triggers']))
	{
		foreach ($schema[$shardName]['triggers'] as $triggerQuery)
		{
			$output[] = key($triggerQuery);
		}
		$output[] = PHP_EOL;
	}

	if (!empty($schema[$shardName]['procedures']))
	{
		foreach ($schema[$shardName]['procedures'] as $procedureQuery)
		{
			$output[] = key($procedureQuery);
		}
		$output[] = PHP_EOL;
	}

	if (!empty($schema[$shardName]['events']))
	{
		foreach ($schema[$shardName]['events'] as $eventQuery)
		{
			$output[] = key($eventQuery);
		}
		$output[] = PHP_EOL;
	}
}

if ($schemaWarning)
{
	echo PHP_EOL;
	echo "###############################################" . PHP_EOL;
	echo "##  Please fix the above before continuing!  ##" . PHP_EOL;
	echo "###############################################" . PHP_EOL;
	echo PHP_EOL;
}

if ($fastMode)
{
	echo PHP_EOL;
	echo "######################################" . PHP_EOL;
	echo "##  Schema generated in FAST mode!  ##" . PHP_EOL;
	echo "######################################" . PHP_EOL;
	echo PHP_EOL;
}

echo "####################" . PHP_EOL;
echo "##  SCHEMA START  ##" . PHP_EOL;
echo "####################" . PHP_EOL;
echo PHP_EOL;
echo "<b>## Global Settings Disable ##</b>" . PHP_EOL;
echo "SET GLOBAL event_scheduler = OFF;" . PHP_EOL;
echo PHP_EOL;
foreach ($output as $o)
{
	echo $o . PHP_EOL;
}
echo PHP_EOL;
echo "<b>## Global Settings Enable ##</b>" . PHP_EOL;
echo "SET GLOBAL event_scheduler = ON;" . PHP_EOL;
echo PHP_EOL;
echo "##################" . PHP_EOL;
echo "##  SCHEMA END  ##" . PHP_EOL;
echo "##################" . PHP_EOL;
echo '</pre>';
