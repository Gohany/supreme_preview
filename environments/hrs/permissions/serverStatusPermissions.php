<?php
// TODO
// do a return instead
$permissions = [
	'create' => [
                'a' => defaultPermissions::$adminStandard,
                's' => defaultPermissions::$serverSelfStandard,
	],
	'display' => [
		'a' => defaultPermissions::$adminStandard,
		'c' => defaultPermissions::$clientSelfStandard,
		'ac' => defaultPermissions::$accountSelfOrValid,
		's' => defaultPermissions::$serverSelfStandard,
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
			'setAccountPermission' => defaultPermissions::$accountSelfOrValid,
			'setPassword' => defaultPermissions::$accountSelfOrValid,
			'setDisplay' => defaultPermissions::$accountSelfOrValid,
			'setEmail' => defaultPermissions::$accountSelfOrValid
		],
	]
];