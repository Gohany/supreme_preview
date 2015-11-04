<?php
class englishEmailTemplateLanguage implements emailTemplateLanguageInterface
{
	public function getDefaultTemplate()
	{
		return <<<EMAILCONTENT
<html>
	<head>
		<title>Arise Chosen</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<style>
			/* This <style> tag gets copied & pasted into the head of the CSS-inlined document */
			body {
				background: #fff;
				font-family: Arial, sans-serif;
				font-size: 14px;
				margin: 0;
				padding: 0;
				text-align: center;
			}
			table {
				width: 100%;
			}
			table td {
				text-align: center;
			}
		</style>
	</head>
	<body style="background: #fff; font-family: Arial, sans-serif; font-size: 14px; margin: 0; padding: 0; text-align: center;">
                Email
	</body>
</html>
EMAILCONTENT;
	}
}