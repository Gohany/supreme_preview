<?php

class sqlDeploy
{
	
	public $mode;
	public $alters;
	public $creates = array();
	public $errors;
	public $shardMaps;
	public $currentDatabase;
	public $statementInfo;
	public $tar;
	
	const NO_SIGNATURE_KEY = 'noSignature';
	const TAR_DIR = '/var/log/supreme/schema.tar';
	
	public function run()
	{
		
		foreach (glob($_SERVER['_HTDOCS_'] . '/schema/*/*/current.sql') as $schemaPath)
		{
			
			$explode1 = explode('/',$schemaPath);
			$explode2 = array_reverse($explode1);
			array_pop($explode1); //access local path
			$table = $explode2[1];
			$shard = $explode2[2];
			
			foreach ($this->shardMap($shard) as $shardNum => $shardInfo)
			{
				
				
				$database = $shard."_".$shardNum;
				
				if (!$this->showCreateDatabase($shardInfo['key'], $database))
				{
					if (!$this->createDatabase($shardInfo['key'], $database))
					{
						throw new Exception("couldn't create ".$database);
					}
				}
				
				if ($this->selectDB($shardInfo['key'], $database))
				{
					if (!$this->tableExists($shardInfo['key'], $table))
					{
						if (!$this->createTable($shardInfo['key'], $schemaPath, $shard."_".$shardNum))
						{
							throw new Exception("couldn't create ".$table." on ".$database);
						}
					}
				}
			

				$string = implode('/',$explode1). '/alters/*.sql';
				// alters
				foreach(glob($string) as $alterPath)
				{
					$pathInfo = pathinfo($alterPath);
					if ($statementInfo = $this->statementInfo($shardInfo['key'], $table, $pathInfo['filename']))
					{
						$statement = file_get_contents($alterPath);
						if ($this->validStatement($shardInfo['key'], $database, $table, $statement))
						{
							foreach ($statementInfo as $alter_id => $info)
							{
								if (empty($info['signature']))
								{
									$this->alters[self::NO_SIGNATURE_KEY][$shardInfo['key']][$database][$table][$alter_id] = $statement;
								}
								else
								{
									$this->alters[$info['signature']][$shardInfo['key']][$database][$table][$alter_id] = $statement;
								}
							}
						}
						else
						{
							$this->errors[] = $database." had issues with validating statement.";
						}
					}
					else
					{
						$this->errors[] = "Could not get statement info";
					}
				}
			}		
			
		}
		$this->registerStatements();
		
	}
	
	public function registerStatements()
	{
		if (empty($this->errors) && !empty($this->alters[self::NO_SIGNATURE_KEY]))
		{
			
			foreach ($this->alters[self::NO_SIGNATURE_KEY] as $dbKey => $databaseArray)
			{
				foreach ($databaseArray as $database => $tableList)
				{
					foreach ($tableList as $table => $alterList)
					{
						if ($this->selectDB($dbKey, $database))
						{
							foreach ($alterList as $alter_id => $alterStatement)
							{
								print "REGISTERING: DATABASE: ".$database." ".$alterStatement.PHP_EOL;
								$insertRegister = "INSERT IGNORE INTO schemaHistory (`table`, `alter_id`, `signature`, `applied`) VALUES ('".$table."', '".$alter_id."', '".$this->signature()."', '0')";
								if (!mysql::query($dbKey, $insertRegister))
								{
									throw new Exception('Query Failed');
								}
							}
						}
						else
						{
							
							throw new Exception('Unable to select DB');
						}
					}
				}
			}
			print "CREATES: ";
			print_r($this->creates);
		}
	}
	
	public function execute()
	{
		if (empty($this->errors))
		{
			foreach (glob($_SERVER['_HTDOCS_'] . '/schema/signatures/*') as $signaturePath)
			{

				$pathInfo = pathinfo($signaturePath);
				$signature = $pathInfo['filename'];

				if (isset($this->alters[$signature]))
				{
					foreach ($this->alters[$signature] as $dbKey => $databaseArray)
					{
						foreach ($databaseArray as $database => $tableList)
						{
							foreach ($tableList as $table => $alterList)
							{
								if ($this->selectDB($dbKey, $database))
								{
									foreach ($alterList as $alter_id => $alterStatement)
									{
										print "RUNNING: ".$alterStatement.PHP_EOL;
										$insertHistory = "INSERT INTO schemaHistory (`table`, `alter_id`, `signature`, `applied`) VALUES ('".$table."', '".$alter_id."', '".$signature."', '1') ON DUPLICATE KEY UPDATE applied='1'";
										mysql::query($dbKey, $insertHistory);
									}
								}
								else
								{
									throw new Exception('Unable to select DB');
								}
							}
						}
					}
				}
			}
			return true;
		}
		return false;
	}
	
	public function generateNewSchema()
	{
		try
		{
			if (empty($this->errors))
			{
				$this->tar = new PharData(self::TAR_DIR);
				$shards = shard::shardDump();

				foreach ($shards as $shardType => $shardArray)
				{
					$masterShard = reset($shardArray);
					$shardNum = key($shardArray);
					$dbKey = $masterShard['key'];
					$database = $shardType."_".$shardNum;

					// get tables in shard
					if ($this->selectDB($dbKey, $database))
					{

						$query = "SHOW TABLES";
						if ($mysqli = mysql::query($dbKey, $query))
						{
							while ($row = $mysqli->fetch_assoc())
							{
								$table = $row['Tables_in_'.$database];
								if ($resource = $this->showCreateTable($dbKey, $table))
								{
									$showCreate = $resource->fetch_assoc();
									$this->tar->addFromString($shardType.'/'.$table.'/generated.sql', $showCreate['Create Table']);
								}
							}
						}
					}

				}
				
			}
		}
		catch (Exception $e)
		{
			throw $e;
		}
		return false;
	}
	
	public function signature()
	{
		isset($this->signature) || $this->signature = utility::hash(serialize($this->alters[self::NO_SIGNATURE_KEY]).serialize($this->creates));
		return $this->signature;
	}
	
	public function validStatement($dbKey, $database, $table, $statement)
	{
		if ($this->selectDB($dbKey, $database))
		{
			$createTemporary = "CREATE TABLE IF NOT EXISTS migration_".$table." LIKE ".$table;
			$insertTemporary = "INSERT INTO migration_".$table." (SELECT * FROM ".$table.")";
			$migrationAlterStatement = str_ireplace($table, "migration_".$table, $statement);

			if (!mysql::query($dbKey, $createTemporary))
			{
				$this->errors[$dbKey][$table][] = mysql::errorList($dbKey);
				return false;
			}
			if (!mysql::query($dbKey, $insertTemporary))
			{
				$this->errors[$dbKey][$table][] = mysql::errorList($dbKey);
				return false;
			}
			if (!mysql::query($dbKey, $migrationAlterStatement))
			{
				$this->errors[$dbKey][$table][] = mysql::errorList($dbKey);
				return false;
			}

			mysql::query($dbKey, "DROP TABLE migration_".$table);
			return true; //$this->alters[$dbKey][$database][$table][$alter_id] = $statement;
		}
		return false;
	}
	
	public function statementInfo($key, $table, $alter_id)
	{
		$statementInfo = array();
		$alterSelect = "SELECT `table`, `alter_id`, `signature`, `applied` FROM schemaHistory WHERE `table` = '".$table."' AND `alter_id` = '".$alter_id."' and applied = '0'";
		if ($mysqli = mysql::query($key, $alterSelect))
		{
			if ($mysqli->num_rows > 0)
			{
				while ($row = $mysqli->fetch_assoc())
				{
					$statementInfo[$alter_id] = $row;
				}
			}
			else
			{
				$statementInfo[$alter_id] = array(
				    'table' => $table,
				    'alter_id' => $alter_id,
				    'signature' => '',
				    'applied' => '0',
				);
			}
			return $statementInfo;
		}
		else
		{
			print $alterSelect.PHP_EOL;
		}
		return false;
	}
	
	public function createTable($key, $schemaPath, $database)
	{
		$createStatement = file_get_contents($schemaPath);
		$this->creates[$database] = $createStatement;
		return mysql::query($key, $createStatement);
	}
	
	public function createDatabase($key, $database)
	{
		$createStatement = "CREATE DATABASE `".$database."`";
		$this->creates[$database] = $createStatement;
		return mysql::query($key, $createStatement);
	}
	
	public function showCreateTable($key, $table)
	{
		$showCreate = "SHOW CREATE TABLE `".$table."`";
		$mysqli = mysql::query($key, $showCreate);
		if ($mysqli && $mysqli->num_rows == 1)
		{
			return $mysqli;
		}
		return false;
	}
	
	public function showCreateDatabase($key, $database)
	{
		$showCreate = "SHOW CREATE DATABASE `".$database."`";
		$mysqli = mysql::query($key, $showCreate);
		if ($mysqli && $mysqli->num_rows == 1)
		{
			return $mysqli;
		}
		return false;
	}
	
	public function tableExists($key, $table)
	{
		if ($this->showCreateTable($key, $table))
		{
			return true;
		}
		return false;
	}
	
	public function selectDB($key, $database)
	{
		if (isset($this->currentDatabase) && $this->currentDatabase == $database)
		{
			return true;
		}
		if (mysql::selectDB($key, $database))
		{
			$this->currentDatabase = $database;
			return true;
		}
		return false;
	}
	
	public function shardMap($type)
	{
		isset($this->shardMaps[$type]) || $this->shardMaps[$type] = shard::fromType($type);
		return $this->shardMaps[$type];
	}

}