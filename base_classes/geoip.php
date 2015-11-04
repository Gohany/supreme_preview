<?php
class geoip
{
	##
	# Constants
	##
	const CONTINENT_AFRICA = 'AF';
	const CONTINENT_ANTARTICA = 'AN';
	const CONTINENT_ASIA = 'AS';
	const CONTINENT_EUROPE = 'EU';
	const CONTINENT_NORTH_AMERICA = 'NA';
	const CONTINENT_OCEANIA = 'OC';
	const CONTINENT_SOUTH_AMERICA = 'SA';

	private static $anonymousCountryCodes = array('A1', 'A2', 'O1');

	##
	# Properties
	##
	private $ip;
	private $continentCode = false;
	private $countryCode = false;
	private $countryCode3 = false;
	private $countryName = false;
	private $region = false;
	private $city = false;
	private $postalCode = false;
	private $latitude = false;
	private $longitude = false;
	private $haveGeoData = false;

	public function __construct($ip)
	{
		#if (!extension_loaded('geoip'))
		{
			$this->ip = $ip;
			return;
		}

		if (!geoip_db_avail(GEOIP_CITY_EDITION_REV1) && !geoip_db_avail(GEOIP_CITY_EDITION_REV0))
		{
			error::addError('Missing GeoIP City Data');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		$this->ip = $ip;
		if ($ipData = @geoip_record_by_name($this->ip))
		{
			$this->continentCode = $ipData['continent_code'];
			$this->countryCode = $ipData['country_code'];
			$this->countryCode3 = $ipData['country_code3'];
			$this->countryName = $ipData['country_name'];
			$this->region = $ipData['region'];
			$this->city = $ipData['city'];
			$this->postalCode = $ipData['postal_code'];
			$this->latitude = $ipData['latitude'];
			$this->longitude = $ipData['longitude'];
			$this->haveGeoData = true;
		}
	}

	public function getIP()
	{
		return $this->ip;
	}

	public function getContinentCode()
	{
		return $this->continentCode;
	}

	public function getContinentName()
	{
		switch ($this->getContinentCode())
		{
			case self::CONTINENT_AFRICA:
				return 'Africa';
			case self::CONTINENT_ANTARTICA:
				return 'Antartica';
			case self::CONTINENT_ASIA:
				return 'Asia';
			case self::CONTINENT_EUROPE:
				return 'Europe';
			case self::CONTINENT_NORTH_AMERICA:
				return 'North America';
			case self::CONTINENT_OCEANIA:
				return 'Oceania';
			case self::CONTINENT_SOUTH_AMERICA:
				return 'South America';
			default:
				return false;
		}
	}

	public function getCountryCode($threeLetterCode = false)
	{
		if ($threeLetterCode)
		{
			return $this->countryCode3;
		}

		return $this->countryCode;
	}

	public function getCountryName()
	{
		return $this->countryName;
	}

	public function getRegion()
	{
		return $this->region;
	}

	public function getPostalCode()
	{
		return $this->postalCode;
	}

	public function getGeoPoint()
	{
		return new geoPoint($this->latitude, $this->longitude);
	}

	public function getLatitude()
	{
		return $this->latitude;
	}

	public function getLongitude()
	{
		return $this->longitude;
	}

	public function isAnonymous()
	{
		if (in_array($this->getCountryCode(), self::$anonymousCountryCodes))
		{
			return true;
		}

		return false;
	}

	public function isProxy()
	{
		//TODO: add basic proxy detection.
		return false;
	}

	public function hasGeoData()
	{
		return $this->haveGeoData;
	}

	public function isInternalIP()
	{
		if ($this->ip === null)
		{
			return false;
		}

		$ip = explode('.', $this->ip);
		return ($ip[0] == 10)
			|| ($ip[0] == 127)
			|| ($ip[0] == 169 && $ip[1] == 254)
			|| ($ip[0] == 172 && ($ip[1] >= 16 && $ip[1] <= 31))
			|| ($ip[0] == 192 && $ip[1] == 168);
	}

	public static function getClientIP()
	{
		if (empty($_SERVER['REMOTE_ADDR']))
		{
			return false;
		}

		return $_SERVER['REMOTE_ADDR'];
	}
}
class geoPoint
{
	##
	# Constants
	##
	const EARTH_RADIUS_IN_KM = 6371;

	##
	# Properties
	##
	protected $latitude;
	protected $longitude;

	public function __construct($latitude, $longitude)
	{
		$this->latitude = $latitude;
		$this->longitude = $longitude;
	}

	public function getLatitude()
	{
		return $this->latitude;
	}

	public function getLongitude()
	{
		return $this->longitude;
	}

	public function distanceBetween(geoPoint $geoPoint)
	{
		$dLat = deg2rad($this->getLatitude() - $geoPoint->getLatitude());
		$dLon = deg2rad($this->getLongitude() - $geoPoint->getLongitude());

		$a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($this->getLatitude())) * cos(deg2rad($geoPoint->getLatitude())) * sin($dLon / 2) * sin($dLon / 2);
		$c = 2 * asin(sqrt($a));
		$d = self::EARTH_RADIUS_IN_KM * $c;

		return $d;
	}
}