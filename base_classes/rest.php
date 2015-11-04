<?php
/**
 * Class to send RESTful HTTP requests
 */
class rest
{
	const HTTP_SUCCESS_DELETE = 200;
	const HTTP_SUCCESS_GET = 200;
	const HTTP_SUCCESS_POST = 201;
	const HTTP_SUCCESS_PUT = 200;

	private static function MakeGetString($data, $seperator = '&')
	{
		return (is_array($data) === false) ? $data : http_build_query($data, null, $seperator);
	}

	public static function GET($url, $fieldList = null, $optionList = array())
	{
		$url .= '?' . self::MakeGetString($fieldList);

		$optionList = array_replace(array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HEADER => false,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_AUTOREFERER => true,
			CURLOPT_HTTPGET => true,
			), $optionList);

		return self::ProcessCUrl($optionList);
	}

	public static function PUT($url, $fieldList = null, $optionList = array())
	{
		$getString = self::MakeGetString($fieldList);

		$optionList = array_replace(array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HEADER => false,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_AUTOREFERER => true,
			CURLOPT_CUSTOMREQUEST => 'PUT',
			CURLOPT_POSTFIELDS => $getString
			), $optionList);

		return self::ProcessCUrl($optionList);
	}

	public static function POST($url, $fieldList = null, $optionList = array())
	{
		$getString = self::MakeGetString($fieldList);

		$optionList = array_replace(array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HEADER => false,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_AUTOREFERER => true,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $getString
			), $optionList);

		return self::ProcessCUrl($optionList);
	}

	public static function DELETE($url, $fieldList = null, $optionList = array())
	{
		$url .= '?' . self::MakeGetString($fieldList);

		$optionList = array_replace(array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HEADER => false,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_AUTOREFERER => true,
			CURLOPT_CUSTOMREQUEST => 'DELETE'
			), $optionList);

		return self::ProcessCUrl($optionList);
	}

	private static function ProcessCUrl($optionList = array())
	{
		$handle = curl_init();

		if (empty($optionList[CURLOPT_USERAGENT]))
		{
			$optionList[CURLOPT_USERAGENT] = 'Framework SUPREME ' . SUPREME_VERSION;
		}
		curl_setopt_array($handle, $optionList);

		$content = curl_exec($handle);

		if (curl_errno($handle))
		{
			$errorMessage = curl_error($handle);
			curl_close($handle);
			error::addError('CURL Error:' . $errorMessage);
			throw new error(errorCodes::ERROR_REMOTE_API_FAILURE);
		}

		curl_close($handle);
		return $content;
	}
}