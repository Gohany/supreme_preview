<?php
/** @property AuthorizeNetCIM_Response $response */
class authorizeCIM extends authorize
{
	public $customerProfile;
	public $paymentProfile;
	public $paymentProfileId;
	public $AuthorizeNetCIM;
	public $customerProfileId;
	public $AuthorizeNetCustomer;

	const SUCCESS_CODE = 'I00001';
	const VALIDATE_MODE = 'testMode';

	#const VALIDATE_MODE = 'none';

	public static function create($userModel, $paymentProfileId, $invoice)
	{
		$authorize = self::fromCustomerProfileId($userModel->customerProfileId);
		$authorize->loadProperties((array) $invoice);
		$authorize->invoice = $invoice;
		$authorize->paymentProfileId = $paymentProfileId;
		return $authorize;
	}

	public function process()
	{
		$this->sale = new AuthorizeNetTransaction;
		$this->sale->amount = $this->invoice->amount;
		$this->sale->customerProfileId = $this->customerProfileId;
		$this->sale->customerPaymentProfileId = $this->paymentProfileId;
		$this->sale->lineItems = (object) array('itemId' => $this->invoice->purchaseIncrement, 'name' => 'Product', 'description' => '', 'quantity' => 1, 'unitPrice' => $this->invoice->amount);
		$this->response = $this->AuthorizeNetCIM->createCustomerProfileTransaction("AuthCapture", $this->sale);
		$this->logResponse();
	}

	public static function newCustomerProfile(authorizeAIM $authorizeAIM)
	{
		$authorizeCIM = new authorizeCIM;
		$authorizeCIM->createCustomerProfile($authorizeAIM);
		$authorizeCIM->customerProfile->paymentProfiles[] = $authorizeCIM->createCreditCardPaymentProfile($authorizeAIM);

		$authorizeCIM->AuthorizeNetCIM = new AuthorizeNetCIM;
		$response = $authorizeCIM->AuthorizeNetCIM->createCustomerProfile($authorizeCIM->customerProfile, self::VALIDATE_MODE);
		$authorizeCIM->customerProfileId = $response->getCustomerProfileId();

		return $authorizeCIM;
	}

	public static function fromCustomerProfileId($customerProfileId)
	{
		$authorizeCIM = new authorizeCIM;
		$authorizeCIM->AuthorizeNetCIM = new AuthorizeNetCIM;
		$response = $authorizeCIM->AuthorizeNetCIM->getCustomerProfile($customerProfileId);
		$authorizeCIM->customerProfile = (object) utility::objectToArray($response->xml->profile);

		//Make result an array even when only one result
		if (isset($authorizeCIM->customerProfile->paymentProfiles) && !isset($authorizeCIM->customerProfile->paymentProfiles[0]))
		{
			$authorizeCIM->customerProfile->paymentProfiles = array($authorizeCIM->customerProfile->paymentProfiles);
		}

		$authorizeCIM->customerProfileId = $customerProfileId;
		return $authorizeCIM;
	}

	public function addCustomerPaymentProfile($authorizeAIM)
	{
		$paymentProfile = $this->createCreditCardPaymentProfile($authorizeAIM);
		$response = $this->AuthorizeNetCIM->createCustomerPaymentProfile($this->customerProfileId, $paymentProfile);
		return ($response->xml->messages->message->code == self::SUCCESS_CODE);
	}

	public function updateCustomerPaymentProfile($customerPaymentProfileId, $object)
	{
		$paymentProfile = $this->createCreditCardPaymentProfile($object);
		$response = $this->AuthorizeNetCIM->updateCustomerPaymentProfile($this->customerProfileId, $customerPaymentProfileId, $paymentProfile);
		return ($response->xml->messages->message->code == self::SUCCESS_CODE);
	}

	public function deleteCustomerPaymentProfile($customerProfileId, $customerPaymentProfileId)
	{
		$response = $this->AuthorizeNetCIM->deleteCustomerPaymentProfile($customerProfileId, $customerPaymentProfileId);
		return ($response->xml->messages->message->code == self::SUCCESS_CODE);
	}

	public function createCustomerProfile(authorizeAIM $authorizeAIM)
	{
		$this->customerProfile = new AuthorizeNetCustomer;
		$this->customerProfile->description = $authorizeAIM->game . ": " . $authorizeAIM->ident_id;
		$this->customerProfile->merchantCustomerId = $authorizeAIM->cust_id;
		$this->customerProfile->email = $authorizeAIM->email;
	}

	public function createCreditCardPaymentProfile($authorizeAIM)
	{
		$paymentProfile = new AuthorizeNetPaymentProfile;
		$paymentProfile->customerType = "individual";
		$paymentProfile->payment->creditCard->cardNumber = $authorizeAIM->card_num;
		$paymentProfile->payment->creditCard->expirationDate = substr($authorizeAIM->exp_date, 3, 4) . '-' . substr($authorizeAIM->exp_date, 0, 2);
		$paymentProfile->payment->creditCard->cardCode = $authorizeAIM->card_code;
		return $paymentProfile;
	}
}