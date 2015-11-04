<?php
require_once $_SERVER['_HTDOCS_'] . '/libs/authorize/shared/AuthorizeNetRequest.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/authorize/shared/AuthorizeNetResponse.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/authorize/shared/AuthorizeNetTypes.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/authorize/shared/AuthorizeNetXMLResponse.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/authorize/AuthorizeNetAIM.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/authorize/AuthorizeNetARB.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/authorize/AuthorizeNetCIM.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/authorize/AuthorizeNetCP.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/authorize/AuthorizeNetSIM.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/authorize/AuthorizeNetDPM.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/authorize/AuthorizeNetSOAP.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/authorize/AuthorizeNetTD.php';

require_once $_SERVER['_HTDOCS_'] . '/base_classes/authorizeAIM.php';
require_once $_SERVER['_HTDOCS_'] . '/base_classes/authorizeCIM.php';

class authorize
{
	public $description;
	public $customer_ip;
	public $amount;
	public $itemId;
	public $invoice_num;
	public $invoiceIncrement;
	public $trans_id;
	public $sale;
	public $response;
	public $card_code;
	public $invoice;

	public function __construct($data = array())
	{
		$this->loadProperties($data);
	}

	public static function getClassName()
	{
		return get_called_class();
	}

	public function loadProperties($data)
	{
		foreach ($data as $key => $value)
		{
			if (property_exists(self::getClassName(), $key))
			{
				$this->{$key} = $value;
			}
		}
	}

	public function deliverFields()
	{
		$fields = new stdClass;
		foreach ($this->sale->_all_aim_fields as $field)
		{
			if (property_exists(self::getClassName(), $field) && !empty($this->{$field}))
			{
				$fields->{$field} = $this->{$field};
			}
		}
		return $fields;
	}

	public function logResponse()
	{
		if (empty($this->response))
		{
			return false;
		}

		if (is_object($this->response))
		{
			$response = utility::objectToArray($this->response);
		}

		$insertData = array(
			'invoiceIncrement' => $this->invoiceIncrement,
			'response' => serialize($response),
		);

		if (!($invoiceIncrement = dataEngine::write('authorizeResponse', array(
				'action' => 'insert',
				'data' => $insertData))))
		{
			error::addError('Failed to create invoice.');
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}
	}
}
class AuthorizeNetException extends Exception
{

}