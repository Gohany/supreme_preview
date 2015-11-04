<?php
// TODO
// do a return instead
$permissions = [
	'create' => array(
            'c' => defaultPermissions::$clientSelfStandard,
            'a' => defaultPermissions::$adminStandard,
	),
	'display' => [
		'a' => defaultPermissions::$adminStandard,
		'c' => defaultPermissions::$clientSelfStandard,
		'c' => '',
	],
	'modify' => [
		'a' => [
			'adminSetPassword' => defaultPermissions::$adminStandard,
			'adminSetStatus' => defaultPermissions::$adminStandard,
			'adminSetData' => defaultPermissions::$adminStandard,
		],
		'c' => [
			'setPassword' => defaultPermissions::$clientSelfStandard,
			'setDisplay' => defaultPermissions::$clientSelfStandard,
			'setData' => defaultPermissions::$clientSelfStandard
		]
	]
];
