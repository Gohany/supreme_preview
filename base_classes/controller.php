<?PHP
abstract class controller
{
	##
	# Constants
	##
	const ACTION_MODIFY = 'modify';
	const ACTION_CREATE = 'create';

	##
	# Properties
	##
	public $action;
	public $controller;
	public $environment;
	public $input;
	public $output;
	public $statusHeader;
	public $requester;
	public $resources = array();
	public $resourceLocation;
	public $responseHeaders = array();
	public $X_signature;
	public $X_requester;
	public $X_environment;
	public $X_id;
	public $X_slot;
	public $permissions = array();
	public $modifyAction;
        public $authenticationModel;
        public $authToken;

	public function __destruct()
	{
                //
	}

        public function display()
	{
                if (!is_object($this->{static::PRIMARY_MODEL}))
		{
			throw new error(errorCodes::ERROR_OBJECT_NOT_FOUND);
		}
		$this->runOptions(static::PRIMARY_MODEL);
		$this->output = $this->{static::PRIMARY_MODEL};
	}
        
	public function authenticate($action, $controller)
	{
                
		if (is_file($_SERVER['_HTDOCS_'] . '/environments/' . $this->environment . '/permissions/' . $this->environment . 'Permissions.php'))
		{
			require_once $_SERVER['_HTDOCS_'] . '/environments/' . $this->environment . '/permissions/' . $this->environment . 'Permissions.php';
		}

		//Load permissions for this specific request.
		{
			if (is_file($_SERVER['_HTDOCS_'] . '/environments/' . $this->environment . '/permissions/' . $controller . 'Permissions.php'))
			{
				require_once $_SERVER['_HTDOCS_'] . '/environments/' . $this->environment . '/permissions/' . $controller . 'Permissions.php';
			}

			if (!isset($permissions))
			{
				error::addError('Missing Permission Settings');
				throw new error(errorCodes::ERROR_INTERNAL_ERROR);
			}
		}

		$this->permissions = $permissions;

		//Validate request type for this request
		{
			if (!isset($this->permissions[$action]))
			{
				error::addError('No permissions set for requested action.');
				throw new error(errorCodes::ERROR_UNAUTHORIZED_ACTION);
			}

			//If permissions are an empty array, they do not require any permissions / auth to do.  Mostly used for session creation.
			if (empty($this->permissions[$action]))
			{
				return true;
			}

			if (!isset($this->permissions[$action][$this->X_requester]))
			{
				error::addError('No permissions set for requested action from this client type.');
				throw new error(errorCodes::ERROR_UNAUTHORIZED_ACTION);
			}
		}

		//Get permission definition for this specific request
		{
			if ($action == self::ACTION_MODIFY)
			{
				//Validate modify request actions
				{
					if (!isset($this->input['action']))
					{
						error::addError('Missing action');
						throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
					}

					if (!isset($this->permissions[$action][$this->X_requester][$this->input['action']]))
					{
						error::addError('No permissions set for requested action');
						throw new error(errorCodes::ERROR_UNAUTHORIZED_ACTION);
					}
				}

				$permissionDefinition = $this->permissions[$action][$this->X_requester][$this->input['action']];
				$this->modifyAction = $this->input['action'];
			}
			else
			{
				$permissionDefinition = $this->permissions[$action][$this->X_requester];
			}
		}
                
		//Allow requests without login
		if (empty($permissionDefinition))
		{
                        $clientInfo = dataStore::getObject('clientInfo');
                        if ($clientInfo)
                        {
                                $clientInfo->setAuthed();
                        }
			return true;
		}
                
		if (!isset($permissionDefinition['validation']))
		{
			error::addError('Missing auth validation.');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}
                
		//Attempt to load model to validate our: ownership and/or signature
		{
                        
			if (!empty($permissionDefinition['existingModel']) && !empty($this->{$permissionDefinition['existingModel']}) && is_object($this->{$permissionDefinition['existingModel']}))
			{
				$this->authenticationModel = $this->{$permissionDefinition['existingModel']};
			}
			elseif (isset($permissionDefinition['model']))
			{
				//Load our model's class
				if (isset($permissionDefinition['model']['includes']))
				{
					if (!is_array($permissionDefinition['model']['includes']))
					{
						$permissionDefinition['model']['includes'] = array($permissionDefinition['model']['includes']);
					}

					foreach ($permissionDefinition['model']['includes'] as $include)
					{
						$filePath = $_SERVER['_HTDOCS_'] . $include;
						if (!is_file($filePath))
						{
							error::addError('Missing file "' . $filePath . '".');
							throw new error(errorCodes::ERROR_INTERNAL_ERROR);
						}
						require_once $filePath;
					}
				}

				$params = array();
				if (!empty($permissionDefinition['model']['params']))
				{
					foreach ($permissionDefinition['model']['params'] as $param)
					{
						if (!property_exists($this, $param))
						{
							throw new error(errorCodes::ERROR_INTERNAL_ERROR);
						}
						if ($this->$param === null)
						{
							error::addError('Missing value for auth parameter ' . utility::addQuotes($param) . '.');
							throw new error(errorCodes::ERROR_UNAUTHORIZED);
						}
						$params[$param] = $this->{$param};
					}
				}

				if (!method_exists($permissionDefinition['model']['class'], $permissionDefinition['model']['method']))
				{
					error::addError('Missing method "' . $permissionDefinition['model']['class'] . '::' . $permissionDefinition['model']['method'] . '".');
					throw new error(errorCodes::ERROR_INTERNAL_ERROR);
				}

				$this->authenticationModel = call_user_func_array(array($permissionDefinition['model']['class'], $permissionDefinition['model']['method']), $params);
			}
			elseif (isset($this->{static::PRIMARY_MODEL}) && is_object($this->{static::PRIMARY_MODEL}))
			{
				$this->authenticationModel = $this->{static::PRIMARY_MODEL};
			}
			else
			{
				error::addError("Missing model required to authorize.  Did you specify what account/identity you're making your request on?");
				throw new error(errorCodes::ERROR_UNAUTHORIZED);
			}
		}
                
		if (isset($permissionDefinition['ownership']) && !$this->ownershipOfObject($permissionDefinition['ownership'], $this->authenticationModel))
		{
			error::addError('Failed ownership check.');
			throw new error(errorCodes::ERROR_UNAUTHORIZED_ACTION);
		}
                
		if (!$this->validateObject($permissionDefinition['validation'], $this->authenticationModel))
		{
			return false;
		}
                
		$clientInfo = dataStore::getObject('clientInfo');
		if ($clientInfo)
		{
			$clientInfo->setAuthed();
                        dataStore::setObject('clientInfo', $clientInfo);
		}
                
		return true;
	}

	public function ownershipOfObject(array $array, $object)
	{
		if (isset($array['methods']) && is_array($array['methods']) && is_object($object))
		{
			foreach ($array['methods'] as $method => $config)
			{
				$params = array();
				if (isset($config['id']))
				{
					$params[] = $config['id'];
				}

				if (!empty($config['params']))
				{
					foreach ($config['params'] as $param)
					{
						if (!property_exists($this, $param))
						{
							throw new error(errorCodes::ERROR_INTERNAL_ERROR);
						}
						$params[] = $this->{$param};
					}
				}

				if (!method_exists($object, $method))
				{
					throw new error(errorCodes::ERROR_INTERNAL_ERROR);
				}
				$return = call_user_func_array(array($object, $method), $params);
				if (is_object($return))
				{
					if (empty($config['methods']))
					{
						throw new error(errorCodes::ERROR_INTERNAL_ERROR);
					}
					if (!$this->ownershipOfObject($config, $return))
					{
						return false;
					}
				}
				elseif (!is_bool($return))
				{
					throw new error(errorCodes::ERROR_INTERNAL_ERROR);
				}
				if (!$return)
				{
					return false;
				}
			}
		}
		elseif (!empty($array))
		{
			foreach ($array as $method => $config)
			{
				$params = array();
				if (isset($config['id']))
				{
					$params[] = $config['id'];
				}

				if (!empty($config['params']))
				{
					foreach ($config['params'] as $param)
					{
						if (!property_exists($this, $param))
						{
							throw new error(errorCodes::ERROR_INTERNAL_ERROR);
						}
						$params[] = $this->{$param};
					}
				}

				if (!method_exists($object, $method))
				{
					throw new error(errorCodes::ERROR_INTERNAL_ERROR);
				}
				$return = call_user_func_array(array($object, $method), $params);
				if (!is_bool($return))
				{
					throw new error(errorCodes::ERROR_INTERNAL_ERROR);
				}
				if (!$return)
				{
					return false;
				}
			}
		}
		else
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		return true;
	}

	public function validateObject(array $array, $object)
	{
		if (isset($array['methods']) && is_array($array['methods']) && is_object($object))
		{
			foreach ($array['methods'] as $method => $config)
			{
				$params = array();
				if (!empty($config['params']))
				{
					foreach ($config['params'] as $param)
					{
						if (!property_exists($this, $param))
						{
							throw new error(errorCodes::ERROR_INTERNAL_ERROR);
						}
						$params[] = $this->{$param};
					}
				}

				if (!method_exists($object, $method))
				{
					throw new error(errorCodes::ERROR_INTERNAL_ERROR);
				}
				$return = call_user_func_array(array($object, $method), $params);
				if (is_object($return))
				{
					if (empty($config['methods']))
					{
						throw new error(errorCodes::ERROR_INTERNAL_ERROR);
					}
					if (!$this->validateObject($config, $return))
					{
						return false;
					}
				}
				elseif (!is_bool($return))
				{
					throw new error(errorCodes::ERROR_INTERNAL_ERROR);
				}

				if (!$return)
				{
					return false;
				}
			}
		}
		elseif (!empty($array))
		{
			foreach ($array as $method => $config)
			{
				$params = array();
				if (!empty($config['params']))
				{
					foreach ($config['params'] as $param)
					{
						if (!property_exists($this, $param))
						{
							throw new error(errorCodes::ERROR_INTERNAL_ERROR);
						}
						$params[] = $this->{$param};
					}
				}

				if (!method_exists($object, $method))
				{
					throw new error(errorCodes::ERROR_INTERNAL_ERROR);
				}
				$return = call_user_func_array(array($object, $method), $params);
				if (!is_bool($return))
				{
					throw new error(errorCodes::ERROR_INTERNAL_ERROR);
				}
				if (!$return)
				{
					return false;
				}
			}
		}
		else
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		return true;
	}

	public function runOptions($model = false)
	{
		if (!$model)
		{
			$model = static::PRIMARY_MODEL;
		}

		if (isset($this->{$model}) && is_object($this->{$model}) && !empty($this->input))
		{
			foreach ($this->input as $option => $value)
			{
				if (method_exists($model, $option) && property_exists($model, $option))
				{
					$this->{$model}->{$option}($value);
					if ($model != static::PRIMARY_MODEL && isset($this->{$model}->{$option}))
					{
						$this->{static::PRIMARY_MODEL}->$option = $this->{$model}->{$option};
					}
				}
			}
		}
	}

	public function createDifference(array $create_data)
	{
		$difference = array_diff_key($this->create_data, $create_data);

		//allow optional fields
		$difference = array_filter($difference, function($field)
		{
			return (!isset($field['optional']) || !$field['optional']);
		});

		if (!empty($difference))
		{
			$message = is_array($difference) ? implode(', ', array_keys($difference)) : $difference;
			error::addError('Missing required data: ' . $message);
			throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
		}

		return $difference;
	}

	public function createValidation(array $create_data)
	{
		$this->createDifference($create_data);
		$this->writeValidation($create_data);
	}

	public function writeValidation(array $create_data)
	{
		$return = utility::dataValidation($this->create_data, $create_data);
		if (is_array($return) && count($return) > 0)
		{
			foreach ($return as $error)
			{
				error::addError($error['message'], $error['code']);
			}
			error::addError('Failed write validation.');
			throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
		}
	}
        
        /**
	 * Returns the late-static class that calls this.
	 * @return string
	 */
	public static function getClassName()
	{
		return get_called_class();
	}
        
}