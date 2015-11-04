<?php
require_once $_SERVER['_HTDOCS_'] . '/libs/sendgrid/SendGrid.php';
require_once $_SERVER['_HTDOCS_'] . '/base_classes/emailTemplate.php';

class emailManager
{
	const LANGUAGE_DEFAULT = 'default';
	const LANGUAGE_ENGLISH = 'english';
        const DEFAULT_FROM = 'no-reply';
        const DEFAULT_FROM_NAME = 'no-reply';
        const DEFAULT_SUBJECT = 'subject';

	protected $sendgrid;
	protected $language;

	public function __construct($language = self::LANGUAGE_DEFAULT)
	{
		$this->sendgrid = SendGrid::getInstance();
		$this->loadLanguage($language);
	}

	/**
	 *
	 * @return \SendGrid\Email
	 */
	public function getNewEmail()
	{
		$email = new \SendGrid\Email();

		$email->setFrom(self::DEFAULT_FROM);
		$email->setFromName(self::DEFAULT_FROM_NAME);
		$email->setSubject(self::DEFAULT_SUBJECT);

		$email->addFilterSetting('subscriptiontrack', 'enable', 1);
		$email->addFilterSetting('clicktrack', 'enable', 1);
		$email->addFilterSetting('opentrack', 'enable', 1);

		return $email;
	}

	/**
	 *
	 * @param \SendGrid\Email $email
	 * @return bool
	 */
	public function sendEmail(\SendGrid\Email $email)
	{
		if (defined('__SEND_EMAILS__') && !__SEND_EMAILS__)
		{
			return true;
		}
		return $this->sendgrid->web->send($email);
	}

	/**
	 *
	 * @return emailTemplateLanguageInterface
	 */
	public function getTemplates()
	{
		$class = $this->language . 'EmailTemplateLanguage';
		return new $class();
	}

	private function loadLanguage($language)
	{
		$path = $_SERVER['_HTDOCS_'] . '/base_classes/emails/' . $language . '.php';
		if (!is_file($path))
		{
			if ($language == self::LANGUAGE_DEFAULT)
			{
				error::addError('Could not find default language email templates.');
				throw new error(errorCodes::ERROR_INTERNAL_ERROR);
			}
			return $this->loadLanguage(self::LANGUAGE_DEFAULT);
		}
		require_once $path;
		$this->language = $language;
	}
}