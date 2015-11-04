<?PHP
class valid
{
	public static $paymentServices = array(
		'paypal',
		'authorize',
		'authorizecim',
	);
	public static $socialMediaTypes = array(
		socialMedia::TYPE_FACEBOOK,
		socialMedia::TYPE_GOOGLE,
		socialMedia::TYPE_TWITTER,
		socialMedia::TYPE_MOBILE_APP
	);

	public static function statType($type)
	{
		return in_array($type, self::$statTypes);
	}

	public static function expirationDate($date)
	{
		$explode = explode('/', $date);
		if (count($explode) == 2 && self::increment($explode[0]) && self::increment($explode[1]))
		{
			return true;
		}
		return false;
	}
        
	public static function realName($name)
	{
		return (preg_match('/^[\pL]+(?:[\.]{0,1}[\ \-\'][\pL]+)*$/u', $name) > 0);
	}

	/**
	 *
	 * @param type $code
	 * @return boolean
	 */
	public static function code($code)
	{
		return codeGenerator::isCodeSyntacticallyCorrect($code);
	}
        
	/**
	 *
	 * @param type $password
	 * @return boolean
	 */
	public static function password($password)
	{
		return (strlen($password) >= 4);
	}

	/**
	 *
	 * @param type $email
	 * @return boolean
	 */
	public static function email($email)
	{
		return ($email === filter_var($email, FILTER_VALIDATE_EMAIL));
	}

	/**
	 *
	 * @param type $string
	 * @return boolean
	 */
	public static function alphaNumericString($string)
	{
		return ctype_alnum($string);
	}

	/**
	 *
	 * @param type $string
	 * @return boolean
	 */
	public static function string($val, $maxSize = false)
	{
		return is_string($val) && ($maxSize === false || strlen($val) < $maxSize);
	}

	/**
	 *
	 * @param type $string
	 * @return boolean
	 */
	public static function stringOfLength($val, $length)
	{
		return is_string($val) && strlen($val) == $length;
	}

	/**
	 *
	 * @param type $string
	 * @return boolean
	 */
	public static function bool($val)
	{
		return in_array($val, array('true', 'false', '0', '1'));
	}

	/**
	 *
	 * @param type $string
	 * @return boolean
	 */
	public static function nonEmptyArrayOfSQLFields($fields)
	{
		if (!is_array($fields) || empty($fields))
		{
			return false;
		}

		foreach ($fields as $name => $type)
		{
			if (!self::alphaNumericString($name) || !self::sqlDataType($type))
			{
				return false;
			}
		}
		return true;
	}

	/**
	 *
	 * @param type $string
	 * @return boolean
	 */
	public static function hex($string)
	{
		return ctype_xdigit($string);
	}

	/**
	 *
	 * @param type $int
	 * @return boolean
	 */
	public static function tinyint($int)
	{
		return (intval($int) === filter_var($int, FILTER_VALIDATE_INT) && $int >= 0 && $int <= 255);
	}

	/**
	 *
	 * @param type $fqdn
	 * @return boolean
	 */
	public static function fqdn($fqdn)
	{
		return ('http://' . $fqdn === filter_var('http://' . $fqdn, FILTER_VALIDATE_URL));
	}

	/**
	 *
	 * @param type $int
	 * @return boolean
	 */
	public static function oneOrNegativeOne($int)
	{
		return (abs($int) == 1);
	}

	/**
	 *
	 * @param type $string
	 * @return boolean
	 */
	public static function increment($i)
	{
		return (string) (int) $i == $i;
	}

	/**
	 *
	 * @param type $int
	 * @return boolean
	 */
	public static function uniqid($int)
	{
		return (strlen($int) <= 4 && preg_match('/^\d+$/', $int) == 1);
	}

	/**
	 *
	 * @param type $int
	 * @return boolean
	 */
	public static function decimal($int)
	{
		return (preg_match('/^-{0,1}\d+$/', $int) == 1);
	}

	/**
	 *
	 * @param type $ip
	 * @return boolean
	 */
	public static function ip($ip)
	{
		return (ip2long($ip) !== false);
	}

	/**
	 *
	 * @param type $region
	 * @return boolean
	 */
	public static function region($region)
	{
		return region::isRegion($region);
	}

	/**
	 *
	 * @param type $id
	 * @return boolean
	 */
	public static function id($id)
	{
		return (count(explode('.', $id)) == 2);
	}

	/**
	 *
	 * @param type $char
	 * @param type $ignoreSpaces
	 * @return boolean
	 */
	public static function junk($char, $ignoreSpaces = false)
	{
		if ($ignoreSpaces === true && $char === ' ')
		{
			return false;
		}

		return (ctype_cntrl($char) === true || ctype_space($char) === true);
	}

	/**
	 *
	 * @param type $environment
	 * @return boolean
	 */
	public static function environment($environment)
	{
		return in_array($environment, dispatcher::$environments);
	}

	/**
	 *
	 * @param type $version
	 * @return boolean
	 */
	public static function version($version)
	{
		$version = strtolower($version);
		switch ($version)
		{
			case 'current':
			case 'latest':
			case 'live':
				return true;
			default:
				$explode = explode('.', $version);
				if (count($explode) != 4)
				{
					return false;
				}

				foreach ($explode as $k => $v)
				{
					if ($v < 0 || $v > 255)
					{
						return false;
					}
				}
				return true;
		}
	}

	/**
	 *
	 * @param type $hash
	 * @return boolean
	 */
	public static function encryptionHash($hash)
	{
		return (strlen($hash) == 40 && ctype_xdigit($hash));
	}

	/**
	 *
	 * @param type $key
	 * @return boolean
	 */
	public static function encryptionKey($key)
	{
		return (strlen($key) == 32 && ctype_xdigit($key));
	}

	/**
	 *
	 * @param type $checksum
	 * @return boolean
	 */
	public static function checksum($checksum)
	{
		return (strlen($checksum) == 8 && ctype_xdigit($checksum));
	}

	public static function paymentService($service)
	{
		return in_array(strtolower($service), self::$paymentServices);
	}

	public static function luhn($number)
	{
		// Force the value to be a string as this method uses string functions.
		// Converting to an integer may pass PHP_INT_MAX and result in an error!
		$number = (string) $number;

		if (!ctype_digit($number))
		{
			// Luhn can only be used on numbers!
			return false;
		}

		// Check number length
		$length = strlen($number);

		// Checksum of the card number
		$checksum = 0;

		for ($i = $length - 1; $i >= 0; $i -= 2)
		{
			// Add up every 2nd digit, starting from the right
			$checksum += substr($number, $i, 1);
		}

		for ($i = $length - 2; $i >= 0; $i -= 2)
		{
			// Add up every 2nd digit doubled, starting from the right
			$double = substr($number, $i, 1) * 2;

			// Subtract 9 from the double where value is greater than 10
			$checksum += ($double >= 10) ? ($double - 9) : $double;
		}

		// If the checksum is a multiple of 10, the number is valid
		return ($checksum % 10 === 0);
	}

	public static function creditCard($number)
	{
		$cardList = array(
			'default' => array(
				'length' => '13,14,15,16,17,18,19',
				'prefix' => '',
				'luhn' => true
			),
			'american express' => array(
				'length' => '15',
				'prefix' => '3[47]',
				'luhn' => true
			),
			'diners club' => array(
				'length' => '14,16',
				'prefix' => '36|55|30[0-5]',
				'luhn' => true
			),
			'discover' => array(
				'length' => '16',
				'prefix' => '6(?:5|011)',
				'luhn' => true
			),
			'jcb' => array(
				'length' => '15,16',
				'prefix' => '3|1800|2131',
				'luhn' => true
			),
			'maestro' => array(
				'length' => '16,18',
				'prefix' => '50(?:20|38)|6(?:304|759)',
				'luhn' => true
			),
			'mastercard' => array(
				'length' => '16',
				'prefix' => '5[1-5]',
				'luhn' => true
			),
			'visa' => array(
				'length' => '13,16',
				'prefix' => '4',
				'luhn' => true
			)
		);

		// Remove all non-digit characters from the number
		$number = preg_replace('/\D+/', '', $number);

		//Empty string
		if ($number === '')
		{
			return false;
		}

		// Use the default type
		$type = 'default';

		// Check card type
		$type = strtolower($type);
		if (!isset($cardList[$type]))
		{
			return false;
		}

		// Check card number length
		$length = strlen($number);

		// Validate the card length by the card type
		if (!in_array($length, preg_split('/\D+/', $cardList[$type]['length'])))
		{
			return false;
		}

		// Check card number prefix
		if (!preg_match('/^' . $cardList[$type]['prefix'] . '/', $number))
		{
			return false;
		}

		// Luhn check required
		if ($cardList[$type]['luhn'])
		{
			return self::luhn($number);
		}

		return true;
	}

	public static function socialMediaType($type)
	{
		return (in_array($type, self::$socialMediaTypes));
	}

	public static function postalCode($code)
	{
		return (preg_match('/^[a-z0-9\ \-]+$/i', $code) >= 1);
	}
        
}