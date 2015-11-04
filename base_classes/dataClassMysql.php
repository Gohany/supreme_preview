<?php
abstract class dataClassMysql extends dataClass
{
        
	const SELECT = 1;
	const INSERT = 2;
	const UPDATE = 3;
	const DELETE = 4;
	const DUMP = 5;
	#
	const SUCCESS_RETURN_TYPE_BOOL = 1;
	const SUCCESS_RETURN_TYPE_OBJECT = 2;

	public $successReturnType = self::SUCCESS_RETURN_TYPE_BOOL;
	#
	public static $queries = array();

	protected function getAction($actionName)
	{
		if (!defined('static::' . $actionName))
		{
			return false;
		}
		return constant('static::' . $actionName);
	}

	protected function hasOneOf(array $needleList, array $haystack)
	{
		foreach ($haystack as $k => $v)
		{
			if (strpos($k, ' ') !== false)
			{
				$explode = explode(' ', $k);
				$k1 = $explode[0];
			}
			if ((isset($k1) && isset($needleList[$k1])) || isset($needleList[$k]))
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * Changes format of an array of records from php => db using a custom transform function and/or a list of serializedFields defined statically in the deriving class.
	 * @param array $rows
	 * @return array
	 */
	public static function transformArray(&$rows)
	{
		if (method_exists(get_called_class(), 'transform'))
		{
			foreach ($rows as &$row)
			{
				$row = static::transform($row);
			}
			unset($row);
		}

		if (isset(static::$serializedFields))
		{
			foreach ($rows as &$row)
			{
				foreach (static::$serializedFields as $field)
				{
					if (isset($row[$field]))
					{
						$row[$field] = serialize($row[$field]);
					}
				}
			}
		}

		return $rows;
	}

	/**
	 * Changes format of an array of records from db => php using a custom untransform function and/or a list of serializedFields defined statically in the deriving class.
	 * @param array $rows
	 * @return array
	 */
	public static function untransformArray(&$rows)
	{
		if (isset(static::$serializedFields))
		{
			foreach ($rows as &$row)
			{
				foreach (static::$serializedFields as $field)
				{
					if (isset($row[$field]))
					{
						$row[$field] = unserialize($row[$field]);
					}
				}
			}
		}

		if (method_exists(get_called_class(), 'untransform'))
		{
			foreach ($rows as $i => &$row)
			{
				$row = static::untransform($row, $i); //$i will be the table name on join query results
			}
			unset($row);
		}

		return $rows;
	}

	public function dump_mysql($array)
	{
		
		//default action to DUMP
		if (!isset($array['action']))
		{
			$actionName = 'DUMP';
		}

		$action = $this->getAction($actionName);
		if ($action === null || !isset(static::$queries[$action]))
		{
			error::addError('Action "' . $actionName . '" is not a thing in this data class.');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}
		$query = static::$queries[$action];
                $database = $array['database'];
                $dbKey = $array['dbkey'];
		$queries = query::create($database, $dbKey, $query, array());
		if (!$result = $queries->execute(array('errno')))
		{
			error::addError('Dump failed');
			return false;
		}

		$return = array();
		if ($result['success'])
		{
			if ($result['success']->num_rows > 0)
			{
				while ($row = $result['success']->fetch_assoc())
				{
					$return[] = $row;
				}
			}
			$result['success']->free();
		}

		self::untransformArray($return);
		return $return;
	}

	public function read_mysql(array $array)
	{
		if (empty($array['data']))
		{
			error::addError('Missing data');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		if (isset($array['joins']) && is_array($array['joins']))
		{
			$joins = $array['joins'];
		}
		else
		{
			$joins = array();
		}

		//Are we overriding the default action of 'SELECT'?
		if (isset($array['action']))
		{
			$actionName = strtoupper($array['action']);
			//Is this action a thing?
			$action = $this->getAction($actionName);
			if ($action === null)
			{
				error::addError('Action "' . $actionName . '" is not a thing in this data class.');
				throw new error(errorCodes::ERROR_INTERNAL_ERROR);
			}
		}
		else
		{
			$actionName = 'SELECT';
			$action = static::SELECT;
		}

		if (!isset(static::$queries[$action]))
		{
			error::addError('Action "' . $actionName . '" is not a thing in this data class.');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}
		$dataclassInfo = static::$queries[$action];

		if (!$this->hasOneOf($array['data'], $dataclassInfo['where']))
		{
			error::addError('Missing key');
			throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
		}

		//Do we want to replace the limit?
		if (array_key_exists('limit', $array))
		{
			if ($array['limit'] === null || intval($array['limit']) == 0)
			{
				unset($dataclassInfo['limit_multiplier']);
			}
			else
			{
				$dataclassInfo['limit_multiplier'] = intval($array['limit']);
				if (isset($array['page']) && intval($array['page']) > 0)
				{
					$dataclassInfo['page'] = intval($array['page']);
				}
			}
		}

		//Add/Modify the order_by?
		if (array_key_exists('order_by', $array))
		{
			if (empty($array['order_by']))
			{
				unset($dataclassInfo['limit_multiplier']);
			}
			else
			{
				$dataclassInfo['order_by'] = $array['order_by'];
			}
		}

		$dataArray = array($array['data']);
		self::transformArray($dataArray);
		//Do the actual thing
                $database = $array['database'];
                $dbKey = $array['dbkey'];
		$builtQuery = query::create($database, $dbKey, $dataclassInfo, $dataArray, array('*'), $joins);
		if (!$result = $builtQuery->execute(array('errno')))
		{
			return false;
		}

		if (!$result['success'])
		{
			return false;
		}
                
		$return = array();
		if ($result['success']->num_rows > 0)
		{
			if (empty($joins))
			{
				while ($row = $result['success']->fetch_assoc())
				{
					$return[] = $row;
				}
				self::untransformArray($return);
			}
			else
			{
				$fields = $result['success']->fetch_fields();
				while ($row = $result['success']->fetch_row())
				{
					$tables = array();
					foreach ($fields as $i => $field)
					{
						$tables[$field->table][$field->name] = $row[$i];
					}

					$return[] = self::untransformArray($tables);
				}
			}
		}
                
		$result['success']->free();

		if (isset($dataclassInfo['limit_multiplier']) && $dataclassInfo['limit_multiplier'] == 1)
		{
			if (!isset($return[0]))
			{
				return false;
			}
			return $return[0];
		}
                
		return $return;
	}
	/* $array must contain: action, data
	 * if inserting: data can be an array of tuples if multi == true is passed (using utility::isMulti would be unreliable in the case of fields needing serialized)
	 * if deleting or updating: limit can be passed to ensure a limited number of records are affected
	 */

	public function write_mysql($array)
	{
		//action is required
		if (empty($array['action']))
		{
			error::addError('Missing action');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}
		$actionName = strtoupper($array['action']);

		//Is this action a thing?
		$action = $this->getAction($actionName);
		if ($action === null)
		{
			error::addError('Action "' . $actionName . '" is not a thing in this data class.');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		//normalize the data format to be an array of datas (simplifies logic later)
		if (!empty($array['multi']))
		{
			$dataArray = array_values($array['data']);
		}
		else
		{
			$dataArray = array($array['data']);
		}

		//make sure no data is empty
		foreach ($dataArray as $i => $data)
		{
			if (empty($data))
			{
				error::addError('Missing data');
				throw new error(errorCodes::ERROR_INTERNAL_ERROR);
			}
		}

		$query = static::$queries[$action];
		switch (strtolower($query['type']))
		{
			case 'insert':
				foreach ($dataArray as $data)
				{
					//Does our data array use any of our 'where' values?
					if (!$this->hasOneOf($data, $query['tuples']))
					{
						error::addError('Missing tuples in on ' . strtolower($query['type']) . ' ' . $query['table']);
						throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
					}
				}
				self::transformArray($dataArray);
                                //Do the actual thing
                                $database = $array['database'];
                                $dbKey = $array['dbkey'];
				$builtQuery = query::create($database, $dbKey, $query, $dataArray);

				$result = $builtQuery->execute(array('errno', 'insertId', 'affectedRows'));
				if ($result['errno'] == 1062 || !isset($result['insertId']))
				{
					return false;
				}

				//Do we have an auto-increment column?
				if ($result['insertId'] > 0)
				{
					return $result['insertId'];
				}

				switch ($this->successReturnType)
				{
					case self::SUCCESS_RETURN_TYPE_OBJECT:
						return $result;
					case self::SUCCESS_RETURN_TYPE_BOOL:
					default:
						return true;
				}

			case 'update':
			case 'delete':
				//Does our data array use any of our 'where' values?
				if (!$this->hasOneOf($dataArray[0], $query['where']))
				{
					error::addError('Missing key in on ' . strtolower($query['type']) . ' ' . $query['table']);
					throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
				}

				//Do we want to replace the limit?
				if (array_key_exists('limit', $array))
				{
					if ($array['limit'] === null)
					{
						unset($query['limit_multiplier']);
					}
					else
					{
						$query['limit_multiplier'] = intval($array['limit']);
					}
				}

				self::transformArray($dataArray);
                                $database = $array['database'];
                                $dbKey = $array['dbkey'];
				$builtQuery = query::create($database, $dbKey, $query, $dataArray);

				$result = $builtQuery->execute(array('errno', 'affectedRows'));
				if ($result['errno'] == 1062 || !$result['success'])
				{
					return false;
				}

				switch ($this->successReturnType)
				{
					case self::SUCCESS_RETURN_TYPE_OBJECT:
						return $result;
					case self::SUCCESS_RETURN_TYPE_BOOL:
					default:
						return true;
				}

			default:
				error::addError('Unsupported query type ' . $query['type']);
				return false;
		}
	}
}