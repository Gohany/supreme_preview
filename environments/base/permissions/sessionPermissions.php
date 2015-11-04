<?php
$permissions = array(
	'create' => array(
	//No permissions required
	),
	'display' => array(
		'c' => defaultPermissions::$clientSelfStandard,
		'a' => defaultPermissions::$adminStandard,
	),
	'delete' => array(
		'c' => defaultPermissions::$clientSelfStandard,
		'a' => defaultPermissions::$adminStandard,
	)
);