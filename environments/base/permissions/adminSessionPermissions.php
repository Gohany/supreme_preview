<?php
$permissions = array(
	'create' => array(
	//No permissions required
	),
	'display' => array(
		'a' => defaultPermissions::$adminStandard,
	),
	'delete' => array(
		'a' => defaultPermissions::$adminStandard,
	)
);