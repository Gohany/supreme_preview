<?php
/** @property AuthorizeNetAIM_Response $response */
class authorizeAIM extends authorize
{
	public $card_num;
	public $exp_date;
	public $first_name;
	public $last_name;
	public $zip;
	public $email;
	public $game;
	public $cust_id;
	public $ident_id;
	public $saveCard;
	public $card_code;

	public static function create($userModel, $input, $invoice)
	{
		$authorize = new authorizeAIM($input);
		$authorize->cust_id = $userModel->user_id;
		$authorize->loadProperties((array) $invoice);
		return $authorize;
	}

	public function process()
	{
		$this->sale = new AuthorizeNetAIM;
		$this->sale->setFields($this->deliverFields());

		$this->response = $this->sale->authorizeAndCapture();
		$this->logResponse();
	}
}