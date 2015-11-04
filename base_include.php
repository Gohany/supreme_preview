<?php
#Enumeration and global defines
require_once $_SERVER['_HTDOCS_'] . '/base_classes/defines.php';
require_once $_SERVER['_HTDOCS_'] . '/base_classes/errorCodes.php';
require_once $_SERVER['_HTDOCS_'] . '/configs/dataclass.php';

#Configuration
require_once $_SERVER['_HTDOCS_'] . '/configs/config.php';

#SUPREME
require_once $_SERVER['_HTDOCS_'] . '/base_classes/clientInfo.php';
require_once $_SERVER['_HTDOCS_'] . '/base_classes/controller.php';
require_once $_SERVER['_HTDOCS_'] . '/base_classes/defaultPermissions.php';
require_once $_SERVER['_HTDOCS_'] . '/base_classes/dispatcher.php';
require_once $_SERVER['_HTDOCS_'] . '/base_classes/view.php';
require_once $_SERVER['_HTDOCS_'] . '/base_classes/error.php';
require_once $_SERVER['_HTDOCS_'] . '/base_classes/output.php';
require_once $_SERVER['_HTDOCS_'] . '/base_classes/headers.php';
require_once $_SERVER['_HTDOCS_'] . '/base_classes/model.php';
require_once $_SERVER['_HTDOCS_'] . '/base_classes/mysqlLock.php';
require_once $_SERVER['_HTDOCS_'] . '/base_classes/rest.php';
require_once $_SERVER['_HTDOCS_'] . '/base_classes/emailManager.php';
require_once $_SERVER['_HTDOCS_'] . '/base_classes/xml.php';
require_once $_SERVER['_HTDOCS_'] . '/base_classes/asyncTracking.php';
require_once $_SERVER['_HTDOCS_'] . '/base_classes/autoload.php';
require_once $_SERVER['_HTDOCS_'] . '/base_classes/dbKeys.php';
require_once $_SERVER['_HTDOCS_'] . '/base_classes/stringTables.php';

#Utility classes
require_once $_SERVER['_HTDOCS_'] . '/base_classes/utility/utility.php';
require_once $_SERVER['_HTDOCS_'] . '/base_classes/utility/valid.php';
require_once $_SERVER['_HTDOCS_'] . '/base_classes/utility/srp.php';
require_once $_SERVER['_HTDOCS_'] . '/base_classes/utility/log.php';

#Sessions
require_once $_SERVER['_HTDOCS_'] . '/base_classes/models/session.php';
require_once $_SERVER['_HTDOCS_'] . '/base_classes/models/sessions.php';
require_once $_SERVER['_HTDOCS_'] . '/base_classes/models/identity.php';
require_once $_SERVER['_HTDOCS_'] . '/base_classes/controllers/identity.php';

#database
require_once $_SERVER['_HTDOCS_'] . '/base_classes/dataStore.php';
require_once $_SERVER['_HTDOCS_'] . '/base_classes/dataClass.php';
require_once $_SERVER['_HTDOCS_'] . '/base_classes/dataClassMysql.php';
require_once $_SERVER['_HTDOCS_'] . '/base_classes/dataEngine.php';

#mysql
require_once $_SERVER['_HTDOCS_'] . '/base_classes/mysql.php';
require_once $_SERVER['_HTDOCS_'] . '/base_classes/query.php';

#Code-generation
require_once $_SERVER['_HTDOCS_'] . '/base_classes/codeGenerator.php';

#geo-ip and region support
require_once $_SERVER['_HTDOCS_'] . '/base_classes/geoip.php';
require_once $_SERVER['_HTDOCS_'] . '/base_classes/region.php';

#ZMQ support
require_once $_SERVER['_HTDOCS_'] . '/base_classes/zmq.php';

#social media
require_once $_SERVER['_HTDOCS_'] . '/libs/oauth/include.php';
require_once $_SERVER['_HTDOCS_'] . '/base_classes/socialMedia.php';
require_once $_SERVER['_HTDOCS_'] . '/base_classes/socialMediaProviders/facebook.php';
require_once $_SERVER['_HTDOCS_'] . '/base_classes/socialMediaProviders/google.php';
require_once $_SERVER['_HTDOCS_'] . '/base_classes/socialMediaProviders/twitter.php';

#Request logging
require_once $_SERVER['_HTDOCS_'] . '/base_classes/request.php';
require_once $_SERVER['_HTDOCS_'] . '/base_classes/requestParser.php';

#Cache interfaces
require_once $_SERVER['_HTDOCS_'] . '/base_classes/cacheListEngine.php';
require_once $_SERVER['_HTDOCS_'] . '/base_classes/cacheList.php';
require_once $_SERVER['_HTDOCS_'] . '/base_classes/cacheEntryEngine.php';
require_once $_SERVER['_HTDOCS_'] . '/base_classes/cacheEntry.php';

#redis/memcacheD support implementing iCacheEntryEngine and iCacheListEngine
require_once $_SERVER['_HTDOCS_'] . '/base_classes/redis.php';
require_once $_SERVER['_HTDOCS_'] . '/base_classes/memcached.php';

#graphite
require_once $_SERVER['_HTDOCS_'] . '/base_classes/graphite.php';