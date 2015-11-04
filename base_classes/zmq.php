<?php

class zmqIpcPublish
{
	
	public static $instance;
	public $socket;
	public static $poll;
	
	const PROTOCOL = 'ipc';
	const ADDRESS = '*';
	const OBJECT_TYPE = 'zmqIpcPublish';
	
	public function __construct($address = self::ADDRESS, $type = null)
	{
		
		$context = contextHelper::context();
		switch (strtolower($type))
		{
			case 'publish':
				$this->socket = new ZMQSocket($context, ZMQ::SOCKET_PUB);
				$this->socket->bind(self::PROTOCOL.'://'.$address);
				break;
			case 'subscribe':
				$this->socket = new ZMQSocket($context, ZMQ::SOCKET_SUB);
				$this->socket->connect(self::PROTOCOL.'://'.$address);
				break;
			default:
				$this->socket = new ZMQSocket($context, ZMQ::SOCKET_PUB);
				$this->socket->bind(self::PROTOCOL.'://'.$address);
				break;
		}
		
	}
	
	public static function addPoll($address = self::ADDRESS, $type = 'subscribe')
	{
		
		$singleton = self::singleton($address, $type);
		pollHelper::poll()->add($singleton->socket, ZMQ::POLL_IN);

	}
	
	public static function poll()
	{
		
		$readable = $writeable = array();
		
		$events = pollHelper::poll()->poll($readable, $writeable);
		
		return array('events' => $events, 'readable' => $readable, 'writeable' => $writeable);
		
	}
	
	public static function socket($address = self::ADDRESS, $type = 'publish')
	{
		return $singleton = self::singleton($address, $type)->socket;
	}
	
	public static function singleton($address = self::ADDRESS, $type = 'publish')
	{
		if (!(self::$instance = dataStore::getObject($address.$type, self::OBJECT_TYPE)))
		{
			self::$instance = new zmqIpcPublish($address, $type);
			dataStore::setObject($address.$type, self::$instance, self::OBJECT_TYPE);
		}
		return self::$instance;
	}
	
	public static function send($address, $key, $data)
	{
		self::singleton($address, 'publish')->socket->send($key, ZMQ::MODE_SNDMORE);
		self::singleton($address, 'publish')->socket->send($data);
	}
	
	public static function subscribe($address, $key)
	{
		self::singleton($address, 'subscribe')->socket->setSockOpt(ZMQ::SOCKOPT_SUBSCRIBE, $key);
		if ($argNums = func_num_args())
		{
			if ($argNums > 2)
			{
				$arg = func_get_args();
				for ($i = 2; $i < $argNums; $i++)
				{
					self::singleton($address, 'subscribe')->socket->setSockOpt(ZMQ::SOCKOPT_SUBSCRIBE, $arg[$i]);
				}
			}
		}
		
	}
	
	public static function recv($address)
	{
		$return['key'] = self::singleton($address, 'subscribe')->socket->recv(ZMQ::MODE_DONTWAIT);
		$return['data'] = self::singleton($address, 'subscribe')->socket->recv(ZMQ::MODE_DONTWAIT);
		foreach ($return as $value)
		{
			if ($value === false)
			{
				return false;
			}
		}
		return $return;
	}
	
}

class zmqTcpPublish
{
	
	public static $instance;
	public $socket;
	public $subscriptions = array();
	
	const PROTOCOL = 'tcp';
	const ADDRESS = '*';
	const PORT = '10000';
	const OBJECT_TYPE = 'zmqTcpPublish';
	
	public function __construct($address = self::ADDRESS, $port = self::PORT, $type = null)
	{
		
		$context = contextHelper::context();
		switch (strtolower($type))
		{
			case 'publish':
				$this->socket = new ZMQSocket($context, ZMQ::SOCKET_PUB);
				$this->socket->bind(self::PROTOCOL.'://'.$address.':'.$port);
				break;
			case 'subscribe':
				$this->socket = new ZMQSocket($context, ZMQ::SOCKET_SUB);
				$this->socket->connect(self::PROTOCOL.'://'.$address.':'.$port);
				break;
			default:
				$this->socket = new ZMQSocket($context, ZMQ::SOCKET_PUB);
				$this->socket->bind(self::PROTOCOL.'://'.$address.':'.$port);
				break;
		}
		
	}
	
	public static function disconnect($address = self::ADDRESS, $port = self::PORT, $type = 'subscribe')
	{
		self::singleton($address, $port, $type)->socket->disconnect(self::PROTOCOL.'://'.$address.':'.$port);
		self::singleton($address, $port, 'subscribe')->subscriptions = array();
	}
	
	public static function unbind($address = self::ADDRESS, $port = self::PORT, $type = 'publish')
	{
		self::singleton($address, $port, $type)->socket->unbind(self::PROTOCOL.'://'.$address.':'.$port);
	}
	
	public static function singleton($address = self::ADDRESS, $port = self::PORT, $type = 'publish')
	{
		if (!(self::$instance = dataStore::getObject($address.$port.$type, self::OBJECT_TYPE)))
		{
			self::$instance = new zmqTcpPublish($address, $port, $type);
			dataStore::setObject($address.$port.$type, self::$instance, self::OBJECT_TYPE);
		}
		return self::$instance;
	}
	
	public static function socket($address = self::ADDRESS, $port = self::PORT, $type = 'publish')
	{
		return $singleton = self::singleton($address, $port, $type)->socket;
	}
	
	public static function addPoll($address = self::ADDRESS, $port = self::PORT, $type = 'subscribe')
	{
		
		$singleton = self::singleton($address, $port, $type);
		pollHelper::poll()->add($singleton->socket, ZMQ::POLL_IN);

	}
	
	public static function poll()
	{
		
		$readable = $writeable = array();
		$events = pollHelper::poll()->poll($readable, $writeable);
		
		return array('events' => $events, 'readable' => $readable, 'writeable' => $writeable);
		
	}
	
	public static function send($address, $port, $key, $data)
	{
		self::singleton($address, $port, 'publish')->socket->send($key, ZMQ::MODE_SNDMORE);
		self::singleton($address, $port, 'publish')->socket->send($data);
	}
	
	public static function subscribe($address, $port, $key)
	{
		if (!in_array($key, self::singleton($address, $port, 'subscribe')->subscriptions))
		{
			self::singleton($address, $port, 'subscribe')->socket->setSockOpt(ZMQ::SOCKOPT_SUBSCRIBE, $key);
			self::singleton($address, $port, 'subscribe')->subscriptions[] = $key;
		}
		if ($argNums = func_num_args())
		{
			$arg = func_get_args();
			for ($i = 2; $i < $argNums; $i++)
			{
				if (!in_array($arg[$i], self::singleton($address, $port, 'subscribe')->subscriptions))
				{
					self::singleton($address, $port, 'subscribe')->socket->setSockOpt(ZMQ::SOCKOPT_SUBSCRIBE, $arg[$i]);
					self::singleton($address, $port, 'subscribe')->subscriptions[] = $arg[$i];
				}
			}
		}
		
	}
	
	public static function recv($address, $port)
	{
		$return['key'] = self::singleton($address, $port, 'subscribe')->socket->recv(ZMQ::MODE_DONTWAIT);
		$return['data'] = self::singleton($address, $port, 'subscribe')->socket->recv(ZMQ::MODE_DONTWAIT);
		foreach ($return as $value)
		{
			if ($value === false)
			{
				return false;
			}
		}
		return $return;
	}
	
}

class zmqIpc
{
	
	public static $instance;
	public static $poll;
	public $socket;
	
	const OBJECT_TYPE = 'zmqIpc';
	const PROTOCOL = 'ipc';
	const ADDRESS = '*';
	
	public function __construct($address = self::ADDRESS, $type = null)
	{
		
		$context = contextHelper::context();
		switch (strtolower($type))
		{
			case 'push':
				$this->socket = new ZMQSocket($context, ZMQ::SOCKET_PUSH);
				$this->socket->setSockOpt(ZMQ::SOCKOPT_HWM, 10000);
				$this->socket->setSockOpt(ZMQ::SOCKOPT_SNDTIMEO, 0);
				$this->socket->connect(self::PROTOCOL.'://'.$address);
				break;
			case 'pull':
				$this->socket = new ZMQSocket($context, ZMQ::SOCKET_PULL);
				$this->socket->setSockOpt(ZMQ::SOCKOPT_RCVTIMEO, 1000);
				$this->socket->bind(self::PROTOCOL.'://'.$address);
				break;
			default:
				$this->socket = new ZMQSocket($context, ZMQ::SOCKET_PUSH);
				break;
		}
		
	}
	
	public static function addPoll($address = self::ADDRESS, $type = 'push')
	{
		
		$singleton = self::singleton($address, $type);
		if (!(self::$poll = dataStore::getObject($address.$type, self::OBJECT_TYPE.'POLL')))
		{
			self::$poll = new ZMQPoll();
			dataStore::setObject($address.$type, self::$poll, self::OBJECT_TYPE.'POLL');
		}
		
		self::$poll->add($singleton, ZMQ::POLL_IN);

	}
	
	public static function poll($address = self::ADDRESS, $type = 'push')
	{
		
		if (!(self::$poll = dataStore::getObject($address.$type, self::OBJECT_TYPE.'POLL')))
		{
			return false;
		}
		
		$events = self::$poll->poll($readable, $writeable);
		
		return array('events' => $events, 'readable' => $readable, 'writeable' => $writeable);
		
	}
	
	public static function socket($address = self::ADDRESS, $type = 'push')
	{
		return $singleton = self::singleton($address, $type);
	}
	
	static function singleton($address = self::ADDRESS, $type = 'push')
	{
		if (!(self::$instance = dataStore::getObject($address.$type, self::OBJECT_TYPE)))
		{
			self::$instance = new zmqIpc($address, $type);
			dataStore::setObject($address.$type, self::$instance, self::OBJECT_TYPE);
		}
		return self::$instance;
	}
	
	public static function push($address, $data)
	{
		$singleton = self::singleton($address, 'push');
		return $singleton->socket->send($data);
	}
	
	public static function pull($address)
	{
		$singleton = self::singleton($address, 'pull');
		return $singleton->socket->recv();
	}
	
}

class zmqPush
{
	
	public static $instance;
	public static $poll;
	public $socket;
	
	const OBJECT_TYPE = 'zmqPush';
	const PROTOCOL = 'tcp';
	const ADDRESS = '*';
	const PORT = '';
	
	public function __construct($address = self::ADDRESS, $port = self::PORT, $type = null)
	{
		
		$context = contextHelper::context();
		switch (strtolower($type))
		{
			case 'push':
				$this->socket = new ZMQSocket($context, ZMQ::SOCKET_PUSH);
				$addressString = $port == '' ? self::PROTOCOL.'://'.$address : self::PROTOCOL.'://'.$address.':'.$port;
				$this->socket->connect($addressString);
				break;
			case 'pull':
				$this->socket = new ZMQSocket($context, ZMQ::SOCKET_PULL);
				$this->socket->bind(self::PROTOCOL.'://'.$address.':'.$port);
				break;
			default:
				$this->socket = new ZMQSocket($context, ZMQ::SOCKET_PUSH);
				break;
		}
		
	}
	
//	public function __destruct()
//	{
//		$this->socket->disconnect();
//	}
	
	static function singleton($address = self::ADDRESS, $port = self::PORT, $type = 'push')
	{
		if (!(self::$instance = dataStore::getObject($address.$port.$type, self::OBJECT_TYPE)))
		{
			self::$instance = new zmqPush($address, $port, $type);
			dataStore::setObject($address.$port.$type, self::$instance, self::OBJECT_TYPE);
		}
		return self::$instance;
	}
	
	public static function push($address, $port, $data)
	{
		$singleton = self::singleton($address, $port, 'push');
		return $singleton->socket->send($data);
	}
	
	public static function pull($address, $port)
	{
		$singleton = self::singleton($address, $port, 'pull');
		return $singleton->socket->recv();
	}
	
}

class pollHelper
{
	
	public static $instance;
	public $poll;
	
	public static function poll()
	{
		if (!isset(self::singleton()->poll))
		{
			self::singleton()->poll = new ZMQPoll();
		}
		return self::singleton()->poll;
	}
	
	public static function singleton()
	{
		self::$instance || self::$instance = new pollHelper();
		return self::$instance;
	}
	
}

class contextHelper
{
	
	public static $instance;
	public $context;
	
	public static function context()
	{
		
		if (!isset(self::singleton()->context))
		{
			self::singleton()->context = new ZMQContext();
		}
		return self::singleton()->context;
	}
	
	public static function singleton()
	{
		self::$instance || self::$instance = new contextHelper();
		return self::$instance;
	}
	
}