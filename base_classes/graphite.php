<?php
/**
 * @property graphite $instance 
 */
class graphite
{
	const OBJECT_TYPE = 'graphite';
	const DEFAULT_GRAPHITE_HOST = 'graphite';
	const DEFAULT_GRAPHITE_PORT = '2003';
	
	public static $instance;
	
	private $data = array();
	private $host = self::DEFAULT_GRAPHITE_HOST;
	private $port = self::DEFAULT_GRAPHITE_PORT;
	
	public function __construct($data = array())
	{
		if(isset($data['host']))
		{
			$this->host = $data['host'];
		}
		
		if(isset($data['port']))
		{
			$this->port = $data['port'];
		}
	}
	
	public function __destruct()
	{
		$this->commitToGraphite();
	}
	
	public static function instance($instance = self::OBJECT_TYPE , $data = array())
	{
		if (!(self::$instance = dataStore::getObject($instance, self::OBJECT_TYPE)))
		{
			self::$instance = new graphite($data);
			dataStore::setObject($instance, self::$instance, self::OBJECT_TYPE);
		}

		return self::$instance;
	}
	
	public static function add($key , $value , $timestamp)
	{
		return self::instance()->_add($key, $value , $timestamp);
	}
	
	public function _add($key , $value , $timestamp)
	{
		if((string) $key !== $key || (int) $value != $value || (string) (int) $timestamp != $timestamp)
		{
			error::addError('Invalid Key - Value - Timestamp.');
			throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
		}
		
		$key = (string) $key;
		$value = (int) $value;
		$timestamp = (string) (int) $timestamp;
		
		if(isset($this->data[$timestamp][$key]))
		{
			$this->data[$timestamp][$key] += $value;
		}
		else
		{
			$this->data[$timestamp][$key] = $value;
		}
		
		return true;
	}
	
	//This function commits all key - value - timestamps in $this->data to graphite.
	//It then resets $this->data to prevent any future commits from duplicating keys.
	public function commitToGraphite()
	{
		$conn = fsockopen($this->host , $this->port);
		
		foreach($this->data as $timestamp => $data)
		{
			foreach($data as $key => $value)
			{
				$line = $key . ' ' . $value . ' ' . $timestamp . PHP_EOL;
				fwrite($conn, $line);
			}
		}
		
		fclose($conn);
		
		$this->data = array();
		
		return true;
	}
}