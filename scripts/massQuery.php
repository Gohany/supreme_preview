<?PHP
error_reporting(E_ALL);
require_once $_SERVER['_HTDOCS_'] . '/base_include.php';
?>
<form method="POST" action="">
	<select name="shardType">
		<?PHP
		$shardList = array_keys(shard::shardDump());
		sort($shardList);

		foreach ($shardList as $shardName)
		{
			if (isset($_POST['shardType']) && $_POST['shardType'] == $shardName)
			{
				?>
				<option selected="selected"><?= $shardName; ?></option>
				<?PHP
			}
			else
			{
				?>
				<option><?= $shardName; ?></option>
				<?PHP
			}
		}
		?>
	</select><br />
	<textarea name="sql" style="width: 680px; height: 220px;"></textarea><br/>
	<input type="submit">
	Show Select Results: <input type="checkbox" name="showSelectResults"<?= (isset($_POST['showSelectResults'])) ? ' checked="checked"' : ''; ?>/>
	Sum Select Results: <input type="checkbox" name="sumSelectResults"<?= (isset($_POST['sumSelectResults'])) ? ' checked="checked"' : ''; ?>/>
	Flush Cache after Queries: <input type="checkbox" name="flushCache"<?= (isset($_POST['flushCache'])) ? ' checked="checked"' : ''; ?>/>

</form>
<style>
	table
	{
		border: 1px solid black;
		border-collapse: collapse;
		margin-top: 5px
	}
	th
	{
		border: 1px solid black;
		padding: 10px
	}
	tr
	{
		border: 1px solid black
	}
	td
	{
		border: 1px solid black;
		padding: 3px
	}
</style>
<?PHP
if (count($_POST) > 0)
{
	$query = $_POST['sql'];
	if ($shards = shard::fromType($_POST['shardType']))
	{
		foreach ($shards as $shard => $shardArray)
		{
			echo '<div style="border-width: 1px; border-style: solid; margin-bottom: 10px; margin-left: 5px; padding: 5px;"><pre>';
			echo 'Shard: ' . $shardArray['key'] . '<br />' . PHP_EOL;
			$statement = 'DELIMITER ;' . PHP_EOL . str_replace(array('|shard|', '|shardNumber|'), array($_POST['shardType'] . '_' . $shard, $shard), $query);

			preg_match_all("/DELIMITER (.+)/i", $statement, $delimiters);
			$queryChunkList = preg_split("/DELIMITER .+/i", $statement);
			array_shift($queryChunkList);

			foreach ($queryChunkList as $key => $queryChunk)
			{
				$delimiter = trim($delimiters[1][$key]);

				$queryChunk = trim($queryChunk);
				if (empty($queryChunk))
				{
					continue;
				}

				$queryList = explode($delimiter, $queryChunk);
				foreach ($queryList as $q)
				{
					$q = trim($q);
					if (empty($q))
					{
						continue;
					}

					$success = mysql::query($shardArray['key'], $q);
					echo 'Query: ' . $q . $delimiter . "<br />" . PHP_EOL;
					if (!$success)
					{
						echo 'Failure! ';
						foreach (mysql::errorList($shardArray['key']) as $error)
						{
							echo $error['errno'] . ': ' . $error['error'] . '<br/>' . PHP_EOL;
						}
						continue;
					}

					echo 'Success! ';
					if (!is_object($success))
					{
						echo '<br/>';
						continue;
					}

					echo $success->num_rows . ' Rows Affected.<br/>';

					if (isset($_POST['showSelectResults']))
					{
						echo "<table><tr><th>";
						$fieldNames = array();
						foreach ($success->fetch_fields() as $f)
						{
							$fieldNames[] = htmlspecialchars($f->name);
						}
						echo implode("</th><th>", $fieldNames);
						echo "</th></tr>";

						for ($i = 0; $i < $success->num_rows; ++$i)
						{
							echo '<tr>';
							foreach ($success->fetch_array(MYSQLI_ASSOC) as $name => $field)
							{
								if (isset($_POST['sumSelectResults']))
								{
									if (is_numeric($field))
									{
										if (empty($summedValues[$name]))
										{
											$summedValues[$name] = $field;
										}
										else
										{
											$summedValues[$name] += $field;
										}
									}
									else
									{
										$summedValues[$name] = '';
									}
								}
								echo '<td>' . htmlspecialchars($field) . '</td>';
							}
							echo '</tr>';
						}
						echo "</table>";
					}

					if (isset($_POST['sumSelectResults']))
					{
						echo "<table><tr><th>";
						$fieldNames = array();
						foreach ($success->fetch_fields() as $f)
						{
							$fieldNames[] = htmlspecialchars($f->name);
						}
						echo implode("</th><th>", $fieldNames);
						echo "</th></tr>";
						echo '<tr>';
						foreach ($summedValues as $count)
						{
							echo '<td>' . $count . '</td>';
						}
						echo '</tr>';
						echo "</table>";
					}
				}
			}
			echo '</pre></div>';
		}

		if (isset($_POST['flushCache']))
		{
			redisPool::flushDB(redisKey::DATABASE_CACHE_DATA);
			memcachedPool::flushAll();
		}
	}
	else
	{
		echo "<h3>No shards</h3>";
	}
}