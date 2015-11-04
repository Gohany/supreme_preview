<!DOCTYPE html>
<html>
	<head>
		<title>IP 2 CountryCode</title>
	</head>
	<body>
		<form method="POST" action="">
			IP List:<br/>
			<textarea style="width: 200px; height: 300px;" name="ipList"><?= (!empty($_REQUEST['ipList'])) ? htmlspecialchars($_REQUEST['ipList']) : ''; ?></textarea><br/>
			<input type="submit" value="Lookup IPs"/>
		</form>
		<?PHP
		require_once $_SERVER['_HTDOCS_'] . '/base_include.php';

		if (!empty($_REQUEST['ipList']))
		{
			?>
			<textarea style="width: 200px; height: 300px;" name="ipList"><?PHP
				$ipList = explode("\n", $_REQUEST['ipList']);
				foreach ($ipList as $ip)
				{
					$geoIP = new geoip($ip);
					if ($geoIP->hasGeoData())
					{
						echo $geoIP->getCountryName() . PHP_EOL;
					}
					else
					{
						echo 'Unknown Country' . PHP_EOL;
					}
				}
				?></textarea>
			<?PHP
		}
		?>
	</body>
</html>