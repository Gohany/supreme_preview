<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8" />
		<title>Configuration Loader</title>
		<style type="text/css" media="screen">
			pre {
				display: inline-block;
				background-color: #EEF;
				border: 1px dashed blue;
				padding: 5px;
			}
			.hide {
				display: none;
			}
			a {
				cursor: pointer;
				color: green;
			}
		</style>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>
		<script>
			$(function()
			{
				$('a').click(function(ev)
				{
					$(this).parent().find("pre").toggle(300);
				});
			});
		</script>
	</head>
	<body>
		<?php
		error_reporting(E_ALL);
		require_once $_SERVER['_HTDOCS_'] . '/base_include.php';

		foreach (glob($_SERVER['_HTDOCS_'] . '/environments/*') as $environmentPath)
		{
			$explode = explode('/', $environmentPath);
			$environment = $explode[count($explode) - 1];

			foreach (glob($_SERVER['_HTDOCS_'] . '/environments/' . $environment . '/settings/*') as $modelSettingsPath)
			{
				$explode = explode('/', $modelSettingsPath);
				$model = $explode[count($explode) - 1];

				$config = array();
				foreach (glob($_SERVER['_HTDOCS_'] . '/environments/' . $environment . '/settings/' . $model . '/*.php') as $filename)
				{
					require $filename;
					$property = basename($filename, '.php');

					$fullPropertyName = $environment . '.' . $model . '.' . $property;
					$fullClassName = $environment . '_' . $model . '_' . $property;

					echo '<div>Updating <a>' . $fullPropertyName . '</a> setting.<br/>' . PHP_EOL;
					if (!isset(${$property}))
					{
						echo '<b>Setting ' . $fullPropertyName . ' missing in file.</b></div>';
						continue;
					}
					$config[$property] = ${$property};
					echo '<pre class="hide">' . $property . ' = ' . htmlspecialchars(print_r(${$property}, true), ENT_QUOTES) . '</pre></div>';
				}

				try
				{
					$serialized = serialize($config);
					if (!dataEngine::write($environment . 'Config', array('action' => 'insert', 'shard' => '000', 'data' => array('dataString' => $serialized, 'cacheKey' => model::CACHE_PREFIX_MODELCONFIG . cacheEntry::CACHE_VERSION . '_' . $model))))
					{
						throw new error('Failed to write');
					}

					//Wipe from cache so it is loaded with proper values next request
					$redisKey = redisPool::getRedisKey(model::CACHE_PREFIX_MODELCONFIG . cacheEntry::CACHE_VERSION . '_' . $model);
					$redisKey->delete();
				}
				catch (Exception $e)
				{
					error::listErrors();
					error::clearErrors();
				}
			}
		}
		?>
	</body>
</html>