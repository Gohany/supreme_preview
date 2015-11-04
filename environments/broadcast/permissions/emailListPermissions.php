<?php
// TODO
// do a return instead
$permissions = [
	'create' => array(
	//No permissions required
	),
	'display' => [
		'a' => defaultPermissions::$adminStandard,
		'c' => defaultPermissions::$noLoginRequired,
		'c' => '',
	],
	'modify' => [
		'a' => [
			'adminSetPassword' => defaultPermissions::$adminStandard,
			'adminSetStatus' => defaultPermissions::$adminStandard,
			'adminSetEmail' => defaultPermissions::$adminStandard,
		],
		'c' => [
			'setPassword' => defaultPermissions::$clientSelfStandard,
			'setDisplay' => defaultPermissions::$clientSelfStandard,
			'setEmail' => defaultPermissions::$clientSelfStandard
		]
	]
];
