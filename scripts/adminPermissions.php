<?php
require_once $_SERVER['_HTDOCS_'] . '/base_include.php';
//require_once $_SERVER['_HTDOCS_'] . '/base_classes/defaultPermissions.php';

echo '<pre>' . print_r(utility::getAllPermissions(dispatcher::REQUESTER_ADMIN), true) . '</pre>';
