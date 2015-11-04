<?php
$permissions = array(
	'create' => array(
	//No permissions required
	),
	'display' => array(
		'c' => defaultPermissions::$clientSelfStandard,
		'ac' => defaultPermissions::$accountSelfStandard,
		'a' => defaultPermissions::$adminStandard,
	),
	'delete' => array(
		'c' => defaultPermissions::$clientSelfStandard,
		'ac' => defaultPermissions::$accountSelfStandard,
		'a' => defaultPermissions::$adminStandard,
	)
);