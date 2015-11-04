<?php

class parseWebhook
{
	const LOG_KEY = 'SendGrindWebhook';
	const LAST_DATAPOINT_KEY = 'SendGrindWebhook_LastDatapoint';
	const METRIC = 'emails';
	/* Graphite Metric keys should be emails.category.event or emails.category.urlclick.url when a url is present */
	
	private $lastDatapointCache;
	private $webhookCache;
	
	public $startTime;
	public $endTime;
	public $webhookJSON;
	public $lastDataPoint;
	public $data = array();
	
	public function __construct()
	{
		$this->startTime = 0;
		$this->endTime = strtotime('-10 minutes');
		$this->webhookCache = RedisPool::getRedisKey(self::LOG_KEY);
		$this->lastDatapointCache = RedisPool::getRedisKey(self::LAST_DATAPOINT_KEY);
	}
	
	public function parse()
	{
		$this->getCache();
		
		if(!is_array($this->webhookJSON) || !isset($this->lastDataPoint))
		{
			throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
		}
		
		$lastTimestamp = 0;
		
		foreach($this->webhookJSON as $json => $score)
		{
			$events = json_decode($json , true);
			if(!is_array($events))
			{
				continue;
			}
			
			foreach($events as $event)
			{
				if(!isset($event['timestamp']) || !isset($event['event']))
				{
					continue;
				}
				
				$timestamp = $event['timestamp'] - ($event['timestamp'] % 60);
				
				//Ensure we're not entering a timestamp that's already been sent to graphite.
				//Bump this up 1 minute if that's the case. 
				if($timestamp == $this->lastDataPoint)
				{
					$timestamp = $timestamp + 60;
				}
				
				$action = $event['event'];
				
				if(isset($event['category']))
				{
					$categories = $event['category'];
				}
				else
				{
					$categories = array('unknown');
				}
				
				$url = null;
				
				//We never use real URLs, graphite keys can only be alphanumeric, so we added identifiers to URLs that we must search for.
				if(isset($event['url']))
				{
					$parsedURL = parse_url($event['url']);
					if(isset($parsedURL['query']))
					{
						$gets = array();
						parse_str($parsedURL['query'] , $gets);
						if(isset($gets['seid']))
						{
							//Ensure it doesn't contain any non-alphanumeric characters.
							$url = preg_replace("/[^A-Za-z0-9 ]/", '', $gets['seid']);
						}
						else
						{
							$url = 'other';
						}
					}
					else
					{
						$url = 'other';
					}
				}
				
				if(!is_array($categories))
				{
					$categories = array($categories);
				}
				
				foreach($categories as $category)
				{
					if(isset($this->data[$timestamp][$category][$action]['value']))
					{
						$this->data[$timestamp][$category][$action]['value'] += 1;
					}
					else
					{
						$this->data[$timestamp][$category][$action]['value'] = 1;
					}

					if(!is_null($url))
					{
						if(isset($this->data[$timestamp][$category][$action]['urls'][$url]['value']))
						{
							$this->data[$timestamp][$category][$action]['urls'][$url]['value'] += 1;
						}
						else
						{
							$this->data[$timestamp][$category][$action]['urls'][$url]['value'] = 1;
						}
					}
				}
				
				
				echo PHP_EOL . $timestamp;
				//Store the greatest timestamp we end on.  This is important on the next request that we don't overlap graphite entries.
				if($timestamp > $lastTimestamp)
				{
					$lastTimestamp = $timestamp;
				}
			}
		}
		
		$this->sendToGraphite();
		$this->saveLastDatapoint($lastTimestamp);
		$this->deleteCache();
	}
	
	private function sendToGraphite()
	{
		if(is_array($this->data))
		{
			foreach($this->data as $timestamp => $data)
			{
				if(!is_array($data))
				{
					continue;
				}
				
				foreach($data as $category => $events)
				{
					if(!is_array($events))
					{
						continue;
					}
					
					foreach($events as $event => $values)
					{
						$key = self::METRIC . '.' . $category . '.' . $event;
						
						if(isset($values['value']) && valid::increment($values['value']))
						{
							graphite::add($key, $values['value'], $timestamp);
							echo PHP_EOL . $key . ' ' . $values['value'];
						}
						
						if(isset($values['urls']) && is_array($values['urls']))
						{
							foreach($values['urls'] as $url => $urlValues)
							{
								if(isset($urlValues['value']) && valid::increment($urlValues['value']))
								{
									$urlKey = self::METRIC . '.' . $category . '.' . 'urlclick' . '.' . $url;
									graphite::add($urlKey, $urlValues['value'], $timestamp);
									echo PHP_EOL . $urlKey . ' ' . $urlValues['value'];
								}
							}
						}
					}
				}
			}
		}
	}
	
	private function getCache()
	{
		if(!is_object($this->webhookCache) || !is_object($this->lastDatapointCache) || !isset($this->startTime) || !isset($this->endTime))
		{
			throw new error(errorCodes::ERROR_COULD_NOT_CONNECT_TO_CACHE);
		}
		
		$this->webhookJSON = $this->webhookCache->zRangeByScore($this->startTime, $this->endTime);
		$this->lastDataPoint = $this->lastDatapointCache->get();
	}
	
	private function deleteCache()
	{
		if(!is_object($this->webhookCache) || !isset($this->startTime) || !isset($this->endTime))
		{
			throw new error(errorCodes::ERROR_COULD_NOT_CONNECT_TO_CACHE);
		}
		
		$this->webhookCache->zDeleteRangeByScore($this->startTime, $this->endTime);
	}
	
	private function saveLastDatapoint($lastTimestamp)
	{
		if(!is_object($this->lastDatapointCache))
		{
			throw new error(errorCodes::ERROR_COULD_NOT_CONNECT_TO_CACHE);
		}
		
		$this->lastDatapointCache->set($lastTimestamp);
	}
}

if (PHP_SAPI !== 'cli')
{
	die('This script must be ran from the command-line.');
}

if (!isset($_SERVER['_HTDOCS_']))
{
	$_SERVER['_HTDOCS_'] = getcwd();
}

require_once $_SERVER['_HTDOCS_'] . '/base_include.php';

$parser = new parseWebhook();
$parser->parse();