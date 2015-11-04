<?php
require_once $_SERVER['_HTDOCS_'] . '/base_include.php';
require_once $_SERVER['_HTDOCS_'] . '/environments/base/controllers/maintenance.php';

if (!empty($_POST['ipList']) && !empty($_POST['status']) && $_POST['status'] == maintenance::STATUS_MAINTENANCE)
{
	$ipArray = explode("\n", $_POST['ipList']);
	foreach ($ipArray as $key => $ip)
	{
		$ip = trim($ip);
		$ipArray[$key] = $ip;
		if (!valid::ip($ip))
		{
			error::addError('Invalid IP: ' . $ip);
			throw new error(errorCodes::ERROR_INVALID_IP);
		}
	}

	$redis = RedisPool::getRedisKey(maintenance::REDIS_KEY);
	$redis->set(serialize($ipArray), array(), redisKey::DATABASE_META);
	$status = maintenance::STATUS_MAINTENANCE;
}
elseif (!empty($_POST['status']) && $_POST['status'] == maintenance::STATUS_ACTIVE)
{
	$redis = RedisPool::getRedisKey(maintenance::REDIS_KEY);
	$redis->delete(redisKey::DATABASE_META);
	$status = maintenance::STATUS_ACTIVE;
}
else
{
	$redis = RedisPool::getRedisKey(maintenance::REDIS_KEY);
	$ipArray = $redis->get(redisKey::DATABASE_META);
	if ($ipArray)
	{
		$ipArray = unserialize($ipArray);
	}
}

if (empty($ipArray))
{
	$status = maintenance::STATUS_ACTIVE;
	$ipArray = array(geoip::getClientIP());
}
else
{
	$status = maintenance::STATUS_MAINTENANCE;
}
?>
<form method="post" action="">
	<div>
		<fieldset>
			<legend>Maintenance Mode</legend>
			<table style="width: 450px;">
				<tbody >
					<tr>
						<td>
							<label>Current Login Status</label>
						</td>
						<td>
							<?php
							switch ($status)
							{
								case maintenance::STATUS_ACTIVE:
									echo 'Login Active';
									break;
								case maintenance::STATUS_MAINTENANCE:
									echo 'Login Maintenance';
									break;
								default:
									echo 'Unknown';
							}
							?>
						</td>
					</tr>
					<tr>
						<td>
							<label>New Login Status</label>
						</td>
						<td>
							<select name="status" onchange="if (this.value === '<?=maintenance::STATUS_MAINTENANCE;?>') { document.getElementById('ipForm').style.display = 'table-row'; } else { document.getElementById('ipForm').style.display = 'none'; }">
								<option <?= ($status == maintenance::STATUS_ACTIVE) ? 'selected="selected"' : ''; ?> value="<?= maintenance::STATUS_ACTIVE; ?>">Login Active</option>
								<option <?= ($status == maintenance::STATUS_MAINTENANCE) ? 'selected="selected"' : ''; ?> value="<?= maintenance::STATUS_MAINTENANCE; ?>">Login Maintenance</option>
							</select>
						</td>
					</tr>
					<tr id="ipForm" <?= ($status == maintenance::STATUS_ACTIVE) ? 'style="display: none;"' : '';?>>
						<td>
							<label>Allowed IPs (One per line)</label>
						</td>
						<td>
							<textarea name="ipList" style="width: 150px; height: 100px;"><?= implode("\n", $ipArray); ?></textarea>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
	</div>
	<div>
		<input type="submit" value="Submit" />
	</div>
</form>