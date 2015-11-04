<?php
class iovation
{
	//Test details
	const DRA_SUB = '678702';
	const DRA_ACCOUNT = "OLTP";
	const DRA_PASSWORD = 'XJBX5TTX';
	const DRA_URL = "https://ci-snare.iovation.com/api/CheckTransactionDetails";
	//Prod details
//	const DRA_SUB = '580700';
//	const DRA_ACCOUNT = "OLTP";
//	const DRA_PASSWORD = 'UGUKE4Q1';
//	const DRA_URL = "https://soap.iovation.com/api/CheckTransactionDetails";

	const RULESET_SIGNUP = 'signup';
	const RULESET_PAYMENT = 'payment';
	const RESULT_APPROVED = 'A';
	const RESULT_DENIED = 'D';
	const RESULT_UNDER_REVIEW = 'R';
	const RESULT_FAILURE = 'F';

	public static function verifyPayment($email, $blackboxCode)
	{
		return self::runVerify($email, $blackboxCode, self::RULESET_PAYMENT);
	}

	public static function verifySignup($email, $blackboxCode)
	{
		return self::runVerify($email, $blackboxCode, self::RULESET_SIGNUP);
	}

	private static function runVerify($email, $blackboxCode, $ruleset)
	{
		$results = self::checkClient($email, $blackboxCode, geoip::getClientIP(), $ruleset);
		if (!$results || !is_array($results) || count($results) == 0 || !isset($results['result']))
		{
			error::addError('Blackbox error: Bad Return.');
			throw new error(errorCodes::ERROR_REMOTE_API_FAILURE);
		}

		switch ($results['result'])
		{
			case self::RESULT_APPROVED:
				break;
			case self::RESULT_FAILURE:
				if (isset($results['error']))
				{
					error::addError('Blackbox error:' . utility::addQuotes($results['error']));
				}
				throw new error(errorCodes::ERROR_PAYMENT_DECLINED);
			case self::RESULT_DENIED:
			case self::RESULT_UNDER_REVIEW:
			default:
				if (isset($results['reason']))
				{
					error::addError('Blackbox error: ' . utility::addQuotes($results['reason']));
				}
				throw new error(errorCodes::ERROR_PAYMENT_DECLINED);
		}

		return true;
	}

	private static function checkClient($username, $blackbox, $ip, $ruleset)
	{
		try
		{
			$client = new SoapClient(NULL, array('connection_timeout' => 3,
				'location' => self::DRA_URL,
				'style' => SOAP_RPC,
				'use' => SOAP_ENCODED,
				'uri' => self::DRA_URL . "#CheckTransactionDetails"
			));

			$return = $client->__soapCall('CheckTransactionDetails', array(
				new SoapParam(self::DRA_SUB, 'subscriberid')
				, new SoapParam(self::DRA_ACCOUNT, 'subscriberaccount')
				, new SoapParam(self::DRA_PASSWORD, 'subscriberpasscode')
				, new SoapParam($username, 'accountcode')
				, new SoapParam($blackbox, 'beginblackbox')
				, new SoapParam($ip, 'enduserip')
				, new SoapParam($ruleset, 'type')
			));

			unset($client);
			return $return;
		}
		catch (SoapFault $e)
		{
			unset($client);
			return array("result" => self::RESULT_FAILURE, "error" => $e);
		}
	}
}