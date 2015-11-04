<?php
/**
 * @property region $instance Description
 */
class region
{
	##
	# Constants
	##
	const CACHE_KEY = 'regionList';
	const CACHE_EXPIRATION = 86400;
	//Region constants
	const REGION_DEFAULT = 'USE';
	const REGION_ANY = 'ANY';
	const REGION_UNKNOWN = 'unknown';

	##
	# Singleton
	##
	private static $instance;

	##
	# Properties
	##
	private $regionList;

	public function __construct()
	{
		//If debug, load from file first
		if (defined('__DEBUG__') && __DEBUG__ && $this->getRegionsFromFile())
		{
			return;
		}

		if (!$this->getRegionsFromCache())
		{
			if (!$this->getRegionsFromFile())
			{
				error::addError('Failed to read regions file.');
				throw new error(errorCodes::ERROR_INTERNAL_ERROR);
			}
			$this->setRegionCache();
		}
	}

	private function getRegionsFromFile()
	{
		$file = $_SERVER['_HTDOCS_'] . '/configs/database/regions.json';
		if (!is_file($file) || !$shardList = json_decode(file_get_contents($file), true))
		{
			return false;
		}

		$this->regionList = $shardList;
		return $this->regionList;
	}

	private function getRegionsFromCache()
	{
		$cacheEntry = new cacheEntry(self::CACHE_KEY);
		if (!$cacheEntry->get())
		{
			return false;
		}

		$this->regionList = unserialize($cacheEntry->value);
		return $this->regionList;
	}

	private function setRegionCache()
	{
		$cacheEntry = new cacheEntry(self::CACHE_KEY, serialize($this->regionList), self::CACHE_EXPIRATION);
		return $cacheEntry->set();
	}

	public function getRegionList()
	{
		return $this->regionList;
	}

	private static function singleton()
	{
		self::$instance || self::$instance = new region();
		return self::$instance;
	}

	public static function getDefaultRegion()
	{
		return self::REGION_DEFAULT;
	}

	private static function getClosestRegion(geoPoint $geoPoint, $countryCode = null)
	{
		$closestDistance = null;
		$closestRegion = false;

		$regionList = self::singleton()->getRegionList();
		if (!$regionList)
		{
			error::addError('Missing region list.');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		foreach ($regionList as $regionName => $regionData)
		{
			if (is_null($countryCode) || !isset($regionData['countries']) || (isset($regionData['countries']) && in_array($countryCode, $regionData['countries'])))
			{
				$regionGeoPoint = new geoPoint($regionData['latitude'], $regionData['longitude']);

				$distance = $geoPoint->distanceBetween($regionGeoPoint);
				if ($distance < $closestDistance || $closestDistance === null || $closestRegion === false)
				{
					$closestRegion = $regionName;
					$closestDistance = $distance;
				}
			}
		}

		if (!$closestRegion)
		{
			$closestRegion = static::REGION_UNKNOWN;
		}

		return $closestRegion;
	}

	public static function getRegion($ip)
	{
		$geoip = new geoip($ip);
		if ($geoip->isAnonymous() || !$geoip->hasGeoData())
		{
			return self::getDefaultRegion();
		}

		return self::getClosestRegion($geoip->getGeoPoint(), $geoip->getCountryCode());
	}

	public static function getClientRegion()
	{
		return self::getRegion(geoip::getClientIP());
	}

	public static function isRegion($region)
	{
		return isset(self::singleton()->regionList[$region]);
	}
}