<?php
// TODO
// Define these at runtime
class defaultPermissions
{
	public static $noLoginRequired = array();
        public static $serverSelfStandard = array(
		'model' => array(
			'class' => 'hrsServerModel',
			'method' => 'fromUid',
			'params' => array('X_id'),
			'includes' => '/environments/hrs/models/server.php'
		),
		'existingModel' => 'hrsServerModel',
		'validation' => array(
			'methods' => array(
				'sessions' => array(
					'params' => array(),
					'methods' => array(
                                                'isValidAuthToken' => array(
                                                        'params' => array('X_signature', 'authToken')
                                                ),
					)
				),
			)
		),
		'ownership' => array(
			'methods' => array(
				'isCorrectID' => array(
					'id' => 'uid',
					'params' => array('X_id')
				)
			)
		)
	);
        public static $accountSelfOrValid = array(
		'model' => array(
			'class' => 'hrsAccountModel',
			'method' => 'fromUid',
			'params' => array('X_id'),
			'includes' => '/environments/hrs/models/account.php'
		),
		'existingModel' => 'hrsAccountModel',
		'validation' => array(
			'methods' => array(
				'sessions' => array(
					'params' => array(),
					'methods' => array(
                                                'isValidAuthToken' => array(
                                                        'params' => array('X_signature', 'authToken')
                                                ),
					)
				),
                                'selfOrPermission' => array(
					'params' => array('X_id', 'X_signature', 'authToken', 'environment', 'controller', 'action', 'modifyAction')
				)
			)
		),
	);
        public static $accountSelfStandard = array(
		'model' => array(
			'class' => 'hrsAccountModel',
			'method' => 'fromUid',
			'params' => array('X_id'),
			'includes' => '/environments/hrs/models/account.php'
		),
		'existingModel' => 'hrsAccountModel',
		'validation' => array(
			'methods' => array(
				'sessions' => array(
					'params' => array(),
					'methods' => array(
                                                'isValidAuthToken' => array(
                                                        'params' => array('X_signature', 'authToken')
                                                ),
					)
				),
                                'accountPermission' => array(
					'params' => array('X_signature', 'authToken', 'environment', 'controller', 'action', 'modifyAction')
				)
			)
		),
		'ownership' => array(
			'methods' => array(
				'isCorrectID' => array(
					'id' => 'uid',
					'params' => array('X_id')
				)
			)
		)
	);
	public static $accountStandard = array(
		'model' => array(
			'class' => 'hrsAccountModel',
			'method' => 'fromUid',
			'params' => array('X_id'),
			'includes' => '/environments/hrs/models/account.php'
		),
                'existingModel' => 'hrsAccountModel',
		'validation' => array(
			'methods' => array(
				'sessions' => array(
					'params' => array(),
					'methods' => array(
                                                'isValidAuthToken' => array(
                                                        'params' => array('X_signature', 'authToken')
                                                ),
					)
				),
                                'accountPermission' => array(
					'params' => array('X_signature', 'authToken', 'environment', 'controller', 'action', 'modifyAction')
				)
			)
		)
	);
	public static $clientSelfStandard = array(
		'model' => array(
			'class' => 'baseUserModel',
			'method' => 'fromUser_id',
			'params' => array('X_id'),
			'includes' => '/environments/base/models/user.php'
		),
		'existingModel' => 'baseUserModel',
		'validation' => array(
			'methods' => array(
				'sessions' => array(
					'params' => array(),
					'methods' => array(
                                                'isValidAuthToken' => array(
                                                        'params' => array('X_signature', 'authToken')
                                                ),
					)
				),
			)
		),
		'ownership' => array(
			'methods' => array(
				'isCorrectID' => array(
					'id' => 'user_id',
					'params' => array('X_id')
				)
			)
		)
	);
	public static $clientStandard = array(
		'model' => array(
			'class' => 'baseUserModel',
			'method' => 'fromUser_id',
			'params' => array('X_id'),
			'includes' => '/environments/base/models/user.php'
		),
		'validation' => array(
			'methods' => array(
				'sessions' => array(
					'params' => array(),
					'methods' => array(
//						'isValidSession' => array(
//							'params' => array('X_signature')
//						),
                                                'isValidAuthToken' => array(
                                                        'params' => array('X_signature', 'authToken')
                                                ),
					)
				),
			)
		)
	);
	public static $adminStandard = array(
		'model' => array(
			'class' => 'baseAdminModel',
			'method' => 'fromAdmin_id',
			'params' => array('X_id'),
			'includes' => '/environments/base/models/admin.php'
		),
		'validation' => array(
			'methods' => array(
				'validateAdmin' => array(
					#'params' => array('X_signature', 'environment', 'controller', 'action', 'modifyAction')
					'params' => array('X_signature', 'authToken', 'environment', 'controller', 'action', 'modifyAction')
				)
			)
		)
	);
	public static $adminSelfStandard = array(
		'model' => array(
			'class' => 'baseAdminModel',
			'method' => 'fromAdmin_id',
			'params' => array('X_id'),
			'includes' => '/environments/base/models/admin.php'
		),
		'existingModel' => 'baseAdminModel',
		'validation' => array(
			'methods' => array(
				'validateAdmin' => array(
					#'params' => array('X_signature', 'environment', 'controller', 'action', 'modifyAction')
					'params' => array('X_signature', 'authToken', 'environment', 'controller', 'action', 'modifyAction')
				)
			)
		),
		'ownership' => array(
			'methods' => array(
				'isCorrectID' => array(
					'id' => 'admin_id',
					'params' => array('X_id')
				)
			)
		)
	);

}