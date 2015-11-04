<?php
// TODO
// do a return instead
$permissions = [
	'create' => array(
                'c' => defaultPermissions::$clientSelfStandard,
	),
	'display' => [
		'a' => defaultPermissions::$adminStandard,
		'c' => defaultPermissions::$clientSelfStandard,
		'ac' => defaultPermissions::$accountSelfOrValid,
		#'c' => '',
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
		],
                'ac' => [
			'setAccountPermission' => defaultPermissions::$accountStandard,
			'setPassword' => defaultPermissions::$accountSelfStandard,
			'setDisplay' => defaultPermissions::$accountSelfStandard,
			'setEmail' => defaultPermissions::$accountSelfStandard
		]
	]
];
