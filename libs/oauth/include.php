<?php
require_once $_SERVER['_HTDOCS_'] . '/libs/oauth/ServiceFactory.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/oauth/Common/Consumer/CredentialsInterface.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/oauth/Common/Consumer/Credentials.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/oauth/Common/Exception/Exception.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/oauth/Common/Http/Client/ClientInterface.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/oauth/Common/Http/Client/AbstractClient.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/oauth/Common/Http/Client/CurlClient.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/oauth/Common/Http/Client/StreamClient.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/oauth/Common/Http/Exception/TokenResponseException.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/oauth/Common/Http/Uri/UriInterface.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/oauth/Common/Http/Uri/Uri.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/oauth/Common/Http/Uri/UriFactoryInterface.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/oauth/Common/Http/Uri/UriFactory.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/oauth/Common/Service/ServiceInterface.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/oauth/Common/Service/AbstractService.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/oauth/Common/Storage/Exception/StorageException.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/oauth/Common/Storage/Exception/TokenNotFoundException.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/oauth/Common/Storage/TokenStorageInterface.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/oauth/Common/Storage/Memory.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/oauth/Common/Storage/Redis.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/oauth/Common/Storage/Session.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/oauth/Common/Storage/SymfonySession.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/oauth/Common/Token/TokenInterface.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/oauth/Common/Token/AbstractToken.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/oauth/Common/Token/Exception/ExpiredTokenException.php';

require_once $_SERVER['_HTDOCS_'] . '/libs/oauth/OAuth1/Token/TokenInterface.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/oauth/OAuth1/Token/StdOAuth1Token.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/oauth/OAuth1/Service/ServiceInterface.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/oauth/OAuth1/Service/AbstractService.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/oauth/OAuth1/Signature/Exception/UnsupportedHashAlgorithmException.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/oauth/OAuth1/Signature/SignatureInterface.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/oauth/OAuth1/Signature/Signature.php';

require_once $_SERVER['_HTDOCS_'] . '/libs/oauth/OAuth2/Token/TokenInterface.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/oauth/OAuth2/Token/StdOAuth2Token.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/oauth/OAuth2/Service/ServiceInterface.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/oauth/OAuth2/Service/AbstractService.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/oauth/OAuth2/Service/Exception/InvalidScopeException.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/oauth/OAuth2/Service/Exception/MissingRefreshTokenException.php';

require_once $_SERVER['_HTDOCS_'] . '/libs/oauth/OAuth1/Service/Twitter.php';

require_once $_SERVER['_HTDOCS_'] . '/libs/oauth/OAuth2/Service/Facebook.php';
require_once $_SERVER['_HTDOCS_'] . '/libs/oauth/OAuth2/Service/Google.php';
