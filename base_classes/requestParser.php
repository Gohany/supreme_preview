<?php
class requestParser
{
	const DEFAULT_LIMIT = 1;

	protected $requestList;
	protected $requestTimes = array();
	protected $totalTime = array();
	protected $startTime;
	protected $endTime;
	protected static $requestThresholds = array(
		'requester' => array(
			'c' => 0.75,
			'a' => 0.75,
			'' => 0.75
		),
		'environment' => array(
			'thewpi' => 0.75,
		),
		'controller' => array(
			'authenticate' => 0.75,
			'session' => 0.75
		)
	);

	public function __construct($startUnixTimestamp, $endUnixTimestamp)
	{
		$this->startTime = $startUnixTimestamp;
		$this->endTime = $endUnixTimestamp;

		$this->requestList = self::getRedis()->zRangeByScore($startUnixTimestamp, $endUnixTimestamp);
	}

	public function removeParsedRecords()
	{
		return self::getRedis()->zDeleteRangeByScore($this->startTime, $this->endTime);
	}

	public function parseRequestTimes()
	{
		if (!$this->hasDataToParse())
		{
			return false;
		}

		foreach ($this->requestList as $serialized => $endTime)
		{
			$request = unserialize($serialized);

			//Skip invalid/unfinished requests
			{
				if (!isset($request['controller'], $request['environment'], $request['requester']))
				{
					echo 'Skipped "' . $endTime . ':' . md5($serialized) . '".' . PHP_EOL;
					continue;
				}
				if ($request['startTime'] == $endTime)
				{
					echo 'Not finished.  Skipped.' . PHP_EOL;
					continue;
				}
			}

			$requestTime = $endTime - $request['startTime'];

			$this->totalTime[] = $requestTime;
			$this->requestTimes[$request['method']][] = $requestTime;
			$this->requestTimes[$request['requester']][] = $requestTime;
			$this->requestTimes[$request['environment']][] = $requestTime;
			$this->requestTimes[$request['controller']][] = $requestTime;
			$this->requestTimes[$request['environment'] . '/' . $request['controller']][] = $requestTime;
			$this->requestTimes[$request['requester'] . '/' . $request['environment'] . '/' . $request['controller']][] = $requestTime;

			$requestType = $request['method'] . ':' . $request['requester'] . '/' . $request['environment'] . '/' . $request['controller'];
			$this->requestTimes[$requestType][] = $requestTime;

			foreach (self::$requestThresholds as $type => $limits)
			{
				if (isset($request[$type]) && isset($limits[$request[$type]]))
				{
					$limit = $limits[$request[$type]];
				}
				else
				{
					$limit = self::DEFAULT_LIMIT;
				}

				if ($requestTime > $limit)
				{
					$queryArray = array('type' => $requestType, 'path' => $request['redirectUrl'], 'input' => serialize($request['input']), 'requestTime' => $requestTime);
					#dataEngine::write('requestDeviation', array('action' => 'insert', 'shard' => '000', 'data' => $queryArray));
				}
			}
		}

		return true;
	}

	public function analyzeData()
	{
		if (!$this->hasDataToAnalyze())
		{
			echo 'No data to analyze.' . PHP_EOL;
			return false;
		}

		$averages = array();
		$deviation = array();
		$counts = array();

		sort($this->totalTime, SORT_NUMERIC);
		$count = count($this->totalTime);

		$slice = floor($count * 0.05);

		$this->totalTime = array_slice($this->totalTime, $slice);
		$this->totalTime = array_slice($this->totalTime, 0, count($this->totalTime) - $slice);

		$count -= $slice * 2;

		foreach ($this->requestTimes as $type => $times)
		{
			sort($times);
			$rslice = floor(count($times) * 0.05);

			$times = array_slice($times, $rslice);
			$times = array_slice($times, 0, count($this->requestTimes) - $rslice);
			$this->requestTimes[$type] = $times;
			$typeSums[$type] = array_sum($times);
		}

		$average = array_sum($this->totalTime) / $count;
		print count($this->totalTime) . " total requests" . PHP_EOL;
		print "AVERAGE REQUEST: " . $average . PHP_EOL;

		foreach ($typeSums as $type => $sum)
		{
			$averages[$type] = $sum / count($this->requestTimes[$type]);
		}
		print "AVERAGES: " . PHP_EOL;
		print_r($averages);

		foreach ($this->requestTimes as $type => $times)
		{
			foreach ($times as $time)
			{
				if (!isset($deviation[$type]))
				{
					$deviation[$type] = 0;
				}
				$deviation[$type] += pow(($time - $averages[$type]), 2);

				if (!isset($counts[$type]))
				{
					$counts[$type] = 0;
				}
				$counts[$type] += 1;
			}
		}

		$deviationTotal = 0;

		foreach ($this->totalTime as $time)
		{
			$deviationTotal += pow(($time - $average), 2);
		}

		foreach ($deviation as $type => $deviationTime)
		{
			$standardDeviation[$type] = sqrt($deviationTime / ($counts[$type]));
		}

		$standardDeviationTotal = sqrt($deviationTotal / ($count));

		print "STANDARD DEVIATION PER TYPE: " . PHP_EOL;
		print_r($standardDeviation);
		print "STANDARD DEVIATION TOTAL: " . $standardDeviationTotal . PHP_EOL;

		foreach ($this->requestTimes as $type => $times)
		{
			$totalDeviations = array();
			$totalDeviationsTime = array();
			foreach ($times as $time)
			{
				if ($averages[$type] + ($standardDeviation[$type] * 3) < $time || $averages[$type] - ($standardDeviation[$type] * 3) > $time)
				{
					if (!isset($totalDeviations[$type]))
					{
						$totalDeviations[$type] = 0;
						$totalDeviationsTime[$type] = 0;
					}
					print "$time SECONDS IS ABOVE SD OF " . $standardDeviation[$type] . " x3 FOR $type" . PHP_EOL;
					++$totalDeviations[$type];
					$totalDeviationsTime[$type] += $time;
				}
				if ($average + ($standardDeviationTotal * 3) < $time || $average - ($standardDeviationTotal * 3) > $time)
				{
					if (!isset($totalDeviations[$type]))
					{
						$totalDeviations[$type] = 0;
					}
					print "$time SECONDS IS ABOVE TOTAL SD OF " . $standardDeviationTotal . " x3 FOR $type" . PHP_EOL;
					++$totalDeviations[$type];
				}
			}

			if (isset($totalDeviations[$type]))
			{
				$queryArray = array('currentTime' => date('Y-m-d'), 'hour' => date('G'), 'type' => $type, 'requests' => $totalDeviations[$type], 'requestTime' => $totalDeviationsTime[$type]);
				#dataEngine::write('requestDeviationTimes', array('action' => 'insert', 'shard' => '000', 'data' => $queryArray));
			}
			$queryArray = array('currentDate' => date('Y-m-d'), 'hour' => date('G'), 'type' => $type, 'requests' => count($times), 'totalTime' => $typeSums[$type], 'totalDeviation' => $deviation[$type]);
			#dataEngine::write('requestTotals', array('action' => 'insert', 'shard' => '000', 'data' => $queryArray));
			if (!empty($totalDeviations))
			{
				$queryArray = array('type' => $type, 'standardDeviation' => $standardDeviation[$type], 'totalTime');
			}
		}

		#dataEngine::write('requestTotals', array('action' => 'insert', 'shard' => '000', 'data' => $request));
		#dataEngine::write('requestDeviation', array('action' => 'insert', 'shard' => '000', 'data' => $request));
//		foreach ($this->requestTimes as $name => $typeArray)
//		{
//			print "GRAPH FOR " . $name . PHP_EOL;
//			$this->graphSession($name, $typeArray);
//		}
//		print "GRAPH FOR ALL" . PHP_EOL;
//		$this->graphSession('total', $this->totalTime);
	}

	public function graphSession($name, $array)
	{
		$max = max($array);
		$steps = ($max - min($array)) / 100;

		$graph = array();
		foreach ($array as $time)
		{
			if ($steps > 0)
			{
				$graphLocation = floor(($max - $time) / $steps);

				if (!isset($graph[$graphLocation]))
				{
					$graph[$graphLocation] = 0;
				}
				++$graph[$graphLocation];
			}
		}

		foreach ($graph as $step => $number)
		{
			$queryArray = array('currentDate' => date('Y-m-d'), 'hour' => date('G'), 'type' => $name, 'step' => $step, 'number' => $number);
			#dataEngine::write('requestGraphs', array('action' => 'insert', 'shard' => '000', 'data' => $queryArray));
		}
	}

	protected function hasDataToParse()
	{
		return (count($this->requestList) > 0);
	}

	protected function hasDataToAnalyze()
	{
		return (count($this->totalTime) > 0);
	}

	public function deleteRequests()
	{
		self::getRedis()->zDeleteRangeByScore($this->startTime, $this->endTime);
	}

	private static function getRedis()
	{
		return new redisKey(request::LOG_KEY);
	}
}