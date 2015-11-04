<?php
class codeGenerator
{
	##
	# Constants
	##
	const codeAlphabet = 'ABCDEFGHIJKLMNOPQR0123456789';
	const CodeTranslate = 'ABCDEFGHJKLMNOPRSTUWXY234679';
	const codeLength = 25;

	public static function create()
	{
		//Need key to be a string to use string index operations
		$translationString = self::CodeTranslate;

		//Randomly generate a 25 character string for use as a cdkey
		do
		{
			$randomBytes = bin2hex(utility::getRandomBytes(16));
			$alphabetBase = strlen(self::codeAlphabet);

			$longKey = gmp_strval(gmp_init($randomBytes, 16), $alphabetBase);
			$code = substr($longKey, 0, self::codeLength);
		}
		while (strlen($code) != self::codeLength);

		//Translate our string characters to only use our code alphabet
		$code = strtoupper($code);
		for ($i = 0; $i < self::codeLength; $i++)
		{
			//Find position of current character in alphabet, and take that location (character) in our translation string
			$code[$i] = $translationString[strpos(self::codeAlphabet, $code[$i])];
		}

		return $code;
	}

	/**
	 * Chunks our codes into a string of 5 sets of 5 characters with dashes inbetween
	 * Ex. 0000011111222223333312345 = 00000-11111-22222-33333-12345
	 * @param type $code
	 * @return type
	 */
	public static function chunkCode($code, $chunkSize = 5, $chunkCharacter = '-')
	{
		return substr(chunk_split($code, $chunkSize, $chunkCharacter), 0, -1);
	}

	/**
	 * Strips dashes and spaces from codes, and replaces common typos with valid characters
	 * @param string $code Raw key from user
	 * @return string
	 */
	public static function filterCode($code)
	{
		//Remove excess characters
		$code = str_replace('-', '', $code);

		//Remove all whitespace
		$code = preg_replace('/[\s]+/', '', $code);

		//Convert to upper case
		$code = strtoupper($code);

		//Translate typos
		$code = str_replace('8', 'B', $code);

		$code = str_replace('Q', 'O', $code);
		$code = str_replace('0', 'O', $code);

		$code = str_replace('I', 'L', $code);
		$code = str_replace('1', 'L', $code);

		$code = str_replace('V', 'U', $code);

		$code = str_replace('Z', '2', $code);

		$code = str_replace('5', 'S', $code);

		return $code;
	}

	/**
	 * Checks if a key has only valid characters and is the proper length
	 * @param string $code
	 * @return boolean
	 */
	public static function isCodeSyntacticallyCorrect($code)
	{
		$code = self::filterCode($code);

		if (strlen($code) != self::codeLength)
		{
			return false;
		}

		for ($i = 0; $i < self::codeLength; $i++)
		{
			if (strpos(self::CodeTranslate, $code[$i]) === false)
			{
				return false;
			}
		}

		return true;
	}
}