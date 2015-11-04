<?php

interface emailTemplateLanguageInterface
{

	public function getAccountCreationTemplateText();

	public function getTrialAccountCreationTemplateText();

	public function getBetaKeyInviteTemplateText();

	public function getEmailChangeToOldTemplateText();

	public function getEmailChangeToNewTemplateText();

	public function getForgotPasswordTemplateText();

	public function getPasswordChangeTemplateText();

	public function getRMTPurchaseTemplateText();


	public function getAccountCreationTemplateHTML();

	public function getTrialAccountCreationTemplateHTML();

	public function getBetaKeyInviteTemplateHTML();

	public function getEmailChangeToOldTemplateHTML();

	public function getEmailChangeToNewTemplateHTML();

	public function getForgotPasswordTemplateHTML();

	public function getPasswordChangeTemplateHTML();

	public function getRMTPurchaseTemplateHTML();
}