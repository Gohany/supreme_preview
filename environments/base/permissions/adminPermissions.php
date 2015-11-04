<?php
// TODO
// do a return instead
$permissions = [
	'create' => array(
                'a' => defaultPermissions::$adminStandard,
	),
	'display' => [
		'a' => defaultPermissions::$adminStandard,
	],
	'modify' => [
		'a' => [
                        'adminSetPermission' => defaultPermissions::$adminStandard,
                        'adminRemovePermission' => defaultPermissions::$adminStandard,
                        'setEmail' => defaultPermissions::$adminSelfStandard,
                        'setPassword' => defaultPermissions::$adminSelfStandard,
			'adminSetPassword' => defaultPermissions::$adminStandard,
			'adminSetStatus' => defaultPermissions::$adminStandard,
			'adminSetEmail' => defaultPermissions::$adminStandard,
		],
	]
];
