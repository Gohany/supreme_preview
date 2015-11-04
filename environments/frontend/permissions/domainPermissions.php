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
	],
	'modify' => [
		'a' => [
			'adminSetName' => defaultPermissions::$adminStandard,
		],
		'c' => [
			'setName' => defaultPermissions::$clientSelfStandard,
		]
	]
];
