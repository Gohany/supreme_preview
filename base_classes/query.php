<?php
class query
{
	##
	# Constants
	##
	const TYPE_SELECT = 'select';
	const TYPE_DUMP = 'dump';
	const TYPE_DELETE = 'delete';
	const TYPE_INSERT = 'insert';
	const TYPE_UPDATE = 'update';

	const OPERATOR_EQUALS = '=';
	const OPERATOR_NOT_EQUALS = '!=';
	const OPERATOR_GREATER_THAN = '>';
	const OPERATOR_GREATER_THAN_AND_EQUALS = '>=';
	const OPERATOR_LESS_THAN = '<';
	const OPERATOR_LESS_THAN_AND_EQUALS = '<=';
	const OPERATOR_BETWEEN = 'between';
	const OPERATOR_NOT_BETWEEN = 'not between';
	const OPERATOR_LIKE = 'like';
	const OPERATOR_NOT_LIKE = 'not like';

	const BEHAVIOR_DELAYED = 'DELAYED';
	const BEHAVIOR_LOW_PRIORITY = 'LOW_PRIORITY';
	const BEHAVIOR_HIGH_PRIORITY = 'HIGH_PRIORITY';
	const BEHAVIOR_QUICK = 'QUICK';
	const BEHAVIOR_IGNORE = 'IGNORE';

	##
	# Properties
	##
	private $query;
	private $dbKey;
        private $database;
	private static $operatorList = array(
		self::OPERATOR_EQUALS, self::OPERATOR_NOT_EQUALS,
		self::OPERATOR_GREATER_THAN, self::OPERATOR_GREATER_THAN_AND_EQUALS,
		self::OPERATOR_LESS_THAN, self::OPERATOR_LESS_THAN_AND_EQUALS,
		self::OPERATOR_BETWEEN, self::OPERATOR_NOT_BETWEEN,
		self::OPERATOR_LIKE, self::OPERATOR_NOT_LIKE
	);
	private static $behaviorList = array(
		self::BEHAVIOR_DELAYED,
		self::BEHAVIOR_LOW_PRIORITY,
		self::BEHAVIOR_HIGH_PRIORITY,
		self::BEHAVIOR_QUICK,
		self::BEHAVIOR_IGNORE
	);

	public function __construct($query, $dbKey, $database)
	{
		$this->query = $query;
		$this->dbKey = $dbKey;
                $this->database = $database;
	}

	public function execute($optionList = array())
	{
		//Do we have a query?
		if (empty($this->query))
		{
			error::addError('Attempting to execute an empty query');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		$result = array();

		//Run the Query
		{
			$startTime = microtime(true);
			if (!$result['success'] = mysql::query($this->dbKey, $this->query))
			{
				error::addError('Query error: "' . mysql::error($this->dbKey) . '" on "' . $this->dbKey . '":"' . $this->query . '"');
				throw new error(errorCodes::ERROR_INTERNAL_ERROR);
			}
			$queryExecutionTime = microtime(true) - $startTime;
		}

		//Run options, if we have them
		foreach ($optionList as $option)
		{
			if (method_exists('mysql', $option))
			{
				$result[$option] = call_user_func(array('mysql', $option), $this->dbKey);
			}
		}

		//Log query
		{
			if (defined('__DEBUG_DISPLAY_QUERY_EXECUTION_TIMES__') && __DEBUG_DISPLAY_QUERY_EXECUTION_TIMES__)
			{
				$queryData = array(
					'query' => $this->query,
					'time' => $queryExecutionTime
				);
			}
			else
			{
				$queryData = $this->query;
			}

			if (!$queryLog = dataStore::getArray('__QUERY_ARRAY__', 'queryLog'))
			{
				$queryLog = array($this->dbKey => array($this->database => array()));
			}
			elseif (!isset($queryLog[$this->dbKey][$this->database]))
			{
				$queryLog[$this->dbKey][$this->database] = array();
			}

			$queryLog[$this->dbKey][$this->database][] = $queryData;

			dataStore::setArray('__QUERY_ARRAY__', $queryLog, 'queryLog');
		}

		return $result;
	}

	public static function processMySQLiResult($data, $extractKey = null)
	{
		if (!$data || !isset($data['success']) || !$data['success'])
		{
			return false;
		}

		$mysqli = $data['success'];

		$return = array();
		while ($value = $mysqli->fetch_assoc())
		{
			if ($extractKey !== null)
			{
				$return[] = $value[$extractKey];
			}
			else
			{
				$return[] = $value;
			}
		}

		$mysqli->free();

		return $return;
	}
        
	public static function create($database, $dbKey, array $dataclassInfo, $data, array $fieldList = array('*'), $joinList = array())
	{
		switch (strtolower($dataclassInfo['type']))
		{
			case self::TYPE_DUMP:
				$data = array();
			case self::TYPE_SELECT:
				$query = self::makeSelect($database, $dbKey, $dataclassInfo, current($data), $fieldList, $joinList);
				return new query($query, $dbKey, $database);

			case self::TYPE_INSERT:
				$query = self::makeInsert($database, $dbKey, $dataclassInfo, $data);
				return new query($query, $dbKey, $database);

			case self::TYPE_UPDATE:
				$query = self::makeUpdate($database, $dbKey, $dataclassInfo, current($data));
				return new query($query, $dbKey, $database);

			case self::TYPE_DELETE:
				$query = self::makeDelete($database, $dbKey, $dataclassInfo, current($data));
				return new query($query, $dbKey, $database);
			default:
				throw new error('Not implemented yet!');
		}
	}

	private static function makeSelect($database, $dbKey, array $dataclassInfo, $data, array $fieldList, array $joinList)
	{
		if (empty($fieldList))
		{
			error::addError('Missing data for query');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		$select = 'SELECT ';

		$fieldString = (in_array('*', $fieldList)) ? '* ' : '`' . implode('`, `' . $fieldList) . '` ';

		$from = 'FROM ' . self::getTable($dataclassInfo, $database);

		if (!empty($joinList))
		{
			$from .= self::makeJoins($database, $dataclassInfo, $joinList);
		}

		if (!empty($dataclassInfo['where']))
		{
			$where = self::makeWhere($database, $dbKey, $dataclassInfo, $data);
		}
		else
		{
			$where = '';
		}

		if (isset($dataclassInfo['group_by']) && $dataclassInfo['group_by'])
		{
			$groupBy = ' GROUP BY ' . $dataclassInfo['group_by'];
		}
		else
		{
			$groupBy = '';
		}

		if (isset($dataclassInfo['order_by']) && $dataclassInfo['order_by'])
		{
			$orderBy = ' ORDER BY ' . $dataclassInfo['order_by'];
		}
		else
		{
			$orderBy = '';
		}

		$limit = self::makeLimit($dataclassInfo, $data);

		$query = $select . $fieldString . $from . $where . $groupBy . $orderBy . $limit;

		return $query;
	}

	private static function makeInsert($database, $dbKey, array $dataclassInfo, $data)
	{
		if (empty($data))
		{
			error::addError('Missing data for query');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		$insertInto = 'INSERT ' . self::makeBehaviours($dataclassInfo) . 'INTO ';

		$table = self::getTable($dataclassInfo, $database);

		//Make Tuples
		{
			$columnList = array();
			foreach ($data as $insertGroupValueList)
			{
				if (!is_array($insertGroupValueList))
				{
					continue;
				}

				foreach ($insertGroupValueList as $key => $value)
				{
					if (!array_key_exists($key, $dataclassInfo['tuples']))
					{
						continue;
					}

					if (!utility::fieldTypeCheck($dataclassInfo['tuples'][$key], $value))
					{
						error::addError('Field type failed for ' . utility::addQuotes($value) . '.');
						throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
					}

					if (!in_array($key, $columnList))
					{
						$columnList[] = $key;
					}
				}
			}

			if (empty($columnList))
			{
				error::addError('Missing data for query');
				throw new error(errorCodes::ERROR_INTERNAL_ERROR);
			}

			$valueGroupList = array();
			foreach ($data as $insertGroupNumber => $insertGroupValueList)
			{
				$valueList = array();

				foreach ($columnList as $key)
				{
					if (!array_key_exists($key, $insertGroupValueList))
					{
						if (!isset($dataclassInfo['default_values']) || !$dataclassInfo['default_values'])
						{
							error::addError('Missing a value with default_values turned off!  Need value for ' . utility::addQuotes($key));
							throw new error(errorCodes::ERROR_INTERNAL_ERROR);
						}

						$valueList[] = 'DEFAULT';
					}
					elseif ($insertGroupValueList[$key] === null)
					{
						$valueList[] = 'NULL';
					}
					else
					{
						$valueList[] = "'" . mysql::realEscapeString($dbKey, $insertGroupValueList[$key]) . "'";
					}
				}

				if (empty($valueList))
				{
					continue;
				}

				$valueGroupList[] = implode(', ', $valueList);
			}
		}

		$columns = '`' . implode('`, `', $columnList) . '`';

		if (isset($dataclassInfo['on_duplicate']) && is_array($dataclassInfo['on_duplicate']) && !empty($dataclassInfo['on_duplicate']))
		{
			$onDuplicate = ' ON DUPLICATE KEY UPDATE ' . implode(',', $dataclassInfo['on_duplicate']);
		}
		else
		{
			$onDuplicate = '';
		}

		$query = $insertInto . $table . ' (' . $columns . ') VALUES(' . implode('), (', $valueGroupList) . ')' . $onDuplicate;

		return $query;
	}

	private static function makeDelete($database, $dbKey, array $dataclassInfo, $data)
	{
		$deleteFrom = 'DELETE ' . self::makeBehaviours($dataclassInfo) . 'FROM ';
		$table = self::getTable($dataclassInfo, $database);
		if (!empty($dataclassInfo['where']))
		{
			$where = self::makeWhere($database, $dbKey, $dataclassInfo, $data);
		}
		else
		{
			$where = '';
		}

		$limit = self::makeLimit($dataclassInfo, $data);
		$query = $deleteFrom . $table . $where . $limit;

		return $query;
	}

	private static function makeUpdate($database, $dbKey, array $dataclassInfo, $data)
	{
		$update = 'UPDATE ' . self::makeBehaviours($dataclassInfo);
		$table = self::getTable($dataclassInfo, $database);

		$setList = array();
		foreach ($data as $key => $value)
		{
			if (!array_key_exists($key, $dataclassInfo['set']))
			{
				continue;
			}

			if (!utility::fieldTypeCheck($dataclassInfo['set'][$key], $value))
			{
				error::addError('Field type failed for ' . utility::addQuotes($value) . '.');
				throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
			}

			if ($value === null)
			{
				$setList[] = '`' . $key . "` = NULL";
			}
			else
			{
				$setList[] = '`' . $key . "` = '" . mysql::realEscapeString($dbKey, $value) . "'";
			}
		}

		if (empty($setList))
		{
			error::addError('Empty setList values.');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		$setValues = ' SET ' . implode(', ', $setList);

		if (!empty($dataclassInfo['where']))
		{
			$where = self::makeWhere($database, $dbKey, $dataclassInfo, $data);
		}
		else
		{
			$where = '';
		}

		$limit = self::makeLimit($dataclassInfo, $data);

		$query = $update . $table . $setValues . $where . $limit;

		return $query;
	}

	private static function groupWhereColumns($dataclassInfo, $whereData)
	{
		$formattedWhereData = array();
		foreach ($whereData as $key => $valueList)
		{
			if (!array_key_exists($key, $dataclassInfo['where']))
			{
				continue;
			}

			if (is_array($valueList))
			{
				if (count($valueList) == 0)
				{
					continue;
				}
			}
			else
			{
				$valueList = array($valueList);
			}

			$explode = explode(' ', $key, 2);

			$columnName = $explode[0];

			//Get custom query operator (if one)
			if (isset($explode[1]))
			{
				if (!in_array(strtolower($explode[1]), self::$operatorList))
				{
					error::addError('Unknown query operator ' . $explode[1]);
					throw new error(errorCodes::ERROR_INTERNAL_ERROR);
				}
				$operator = strtoupper($explode[1]);
			}
			else
			{
				$operator = self::OPERATOR_EQUALS;
			}

			foreach ($valueList as $value)
			{
				$fullColumnName = $columnName;
				if (strpos($key, ' ') !== false)
				{
					$fullColumnName .= ' ' . $operator;
				}

				if (!utility::fieldTypeCheck($dataclassInfo['where'][$fullColumnName], $value))
				{
					error::addError('Field type failed for ' . utility::addQuotes($value) . '; should be type ' . utility::addQuotes($dataclassInfo['where'][$fullColumnName]) . '.');
					throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
				}

				if (!array_key_exists($columnName, $formattedWhereData))
				{
					$formattedWhereData[$columnName] = array($operator => array());
				}
				elseif (!array_key_exists($operator, $formattedWhereData[$columnName]))
				{
					$formattedWhereData[$columnName][$operator] = array();
				}

				$formattedWhereData[$columnName][$operator][] = $value;
			}
		}

		return $formattedWhereData;
	}

	private static function makeWhere($database, $dbKey, array $dataclassInfo, $whereData)
	{
		if (empty($whereData))
		{
			return '';
		}

		//Preprocess data and group columns
		$formattedWhereData = self::groupWhereColumns($dataclassInfo, $whereData);
		if (empty($formattedWhereData))
		{
			error::addError('No valid where values found.');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		//Build Where clause
		$whereList = array();
		foreach ($formattedWhereData as $columnName => $data)
		{
			$whereGroup = array();
			foreach ($data as $operator => $valueList)
			{
				switch (strtolower($operator))
				{
					case self::OPERATOR_EQUALS:
					case self::OPERATOR_NOT_EQUALS:
						if (count($valueList) > 1)
						{
							$inString = '`' . $columnName . '` ';
							if ($operator == self::OPERATOR_NOT_EQUALS)
							{
								$inString .= ' NOT ';
							}

							$inList = array();
							foreach ($valueList as $value)
							{
								$inList[] = "'" . mysql::realEscapeString($dbKey, $value) . "'";
							}

							$inString .= 'IN(' . implode(', ', $inList) . ')';

							$whereGroup[] = $inString;
						}
						else
						{
							$whereGroup[] = self::makeColumnOperatorsValueString($database, $dbKey, $columnName, $operator, $valueList[0]);
						}

						break;

					case self::OPERATOR_GREATER_THAN:
					case self::OPERATOR_GREATER_THAN_AND_EQUALS:
					case self::OPERATOR_LESS_THAN:
					case self::OPERATOR_LESS_THAN_AND_EQUALS:
					case self::OPERATOR_LIKE:
					case self::OPERATOR_NOT_LIKE:
						foreach ($valueList as $value)
						{
							$whereGroup[] = self::makeColumnOperatorsValueString($database, $columnName, $operator, $value);
						}

						break;

					case self::OPERATOR_BETWEEN:
					case self::OPERATOR_NOT_BETWEEN:
						//Ensure there are groups of two values
						if (count($valueList) % 2 != 0)
						{
							error::addError('Mismatched between statement.');
							throw new error(errorCodes::ERROR_INTERNAL_ERROR);
						}

						foreach ($valueList as $key => $value)
						{
							if ($key % 2 == 0)
							{
								$betweenString = '`' . $columnName . "` BETWEEN '" . mysql::realEscapeString($dbKey, $value) . "' AND ";
							}
							else
							{
								$betweenString .= "'" . mysql::realEscapeString($dbKey, $value) . "'";
								$whereGroup[] = $betweenString;
							}
						}
						break;

					default:
						error::addError('Unhandled where operator ' . utility::addQuotes($operator) . '.');
						throw new error(errorCodes::ERROR_INTERNAL_ERROR);
				}
			}

			if (count($whereGroup) < 2)
			{
				$whereList[] = implode('', $whereGroup);
			}
			else
			{
				if (count($formattedWhereData) > 1)
				{
					$whereList[] = '(' . implode(' OR ', $whereGroup) . ')';
				}
				else
				{
					$whereList[] = implode(' OR ', $whereGroup);
				}
			}
		}

		return ' WHERE ' . implode(' AND ', $whereList);
	}

	private static function makeColumnOperatorsValueString($database, $dbKey, $columnName, $operator, $value)
	{
		if ($value === null)
		{
			$string = '`' . $columnName . '` IS ';
			if ($operator == self::OPERATOR_NOT_EQUALS || $operator == self::OPERATOR_NOT_LIKE)
			{
				return $string . 'NOT NULL';
			}
			return $string . 'NULL';
		}

		return '`' . $columnName . '` ' . $operator . " '" . mysql::realEscapeString($dbKey, $value) . "'";
	}

	private static function makeJoins($database, $dataclassInfo, $joinList)
	{
		$joinString = '';
		foreach ($joinList as $table)
		{
			$joinType = '';
			if (isset($dataclassInfo['joins']['joinType']))
			{
				$joinType = ' ' . strtoupper($dataclassInfo['joins']['joinType']);
			}
			if (isset($dataclassInfo['joins'][$table]))
			{
				$joinString .= $joinType . ' JOIN `' . self::getTable($dataclassInfo, $database) . '` USING(' . $dataclassInfo['joins'][$table]['using'] . ')';
			}
		}

		return $joinString;
	}

	private static function makeLimit($dataclassInfo, $data)
	{
		if (!isset($dataclassInfo['limit_multiplier']) || $dataclassInfo['limit_multiplier'] <= 0)
		{
			return '';
		}

		$limit = ' LIMIT ';

		$multiplier = 0;

		$formattedData = self::groupWhereColumns($dataclassInfo, $data);
		foreach ($formattedData as $column => $operatorList)
		{
			$columnCount = 0;
			foreach ($operatorList as $operator => $valueList)
			{
				switch (strtolower($operator))
				{
					case self::OPERATOR_BETWEEN:
					case self::OPERATOR_NOT_BETWEEN:
						$columnCount += (count($valueList) / 2);
						break;

					case self::OPERATOR_EQUALS:
					case self::OPERATOR_NOT_EQUALS:
					case self::OPERATOR_GREATER_THAN:
					case self::OPERATOR_GREATER_THAN_AND_EQUALS:
					case self::OPERATOR_LESS_THAN:
					case self::OPERATOR_LESS_THAN_AND_EQUALS:
					case self::OPERATOR_LIKE:
					case self::OPERATOR_NOT_LIKE:
					default:
						$columnCount += (count($valueList));
						break;
				}
			}

			if ($columnCount > 1)
			{
				$multiplier += $columnCount;
			}
		}

		if ($multiplier < 1)
		{
			$multiplier = 1;
		}

		$amount = $dataclassInfo['limit_multiplier'] * $multiplier;

		if (isset($dataclassInfo['page']))
		{
			$page = intval($dataclassInfo['page']);
			if ($page < 1)
			{
				$page = 1;
			}

			$limit .= ($page - 1) * $amount . ', ';
		}

		$limit .= $amount;

		return $limit;
	}

	private static function getTable($dataclassInfo, $database)
	{
		if (!isset($dataclassInfo['table']))
		{
			error::addError('No table specified');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		return '`' . $database . '`.`' . $dataclassInfo['table'] . '`';
	}

	private static function makeBehaviours($dataclassInfo)
	{
		$behaviorList = array();
		foreach (self::$behaviorList as $behavior)
		{
			if ((isset($dataclassInfo[strtolower($behavior)]) && $dataclassInfo[strtolower($behavior)])
				|| (isset($dataclassInfo['behavior']) && strtoupper($dataclassInfo['behavior']) == $behavior))
			{
				$behaviorList[] = $behavior . ' ';
			}
		}

		return implode('', $behaviorList);
	}
}