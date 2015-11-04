<?php

class testUser
{

        public static $instance;
        public $sessionKey;
        public $name;
        public $email;
        public $type;
        public $id;
        public $environment;
        public $requests = 1;

        public static function singleton()
        {
                self::$instance || self::$instance = new testUser;
                return self::$instance;
        }

        public static function set($variable, $value)
        {
                if (property_exists('testUser', $variable))
                {
                        self::singleton()->{$variable} = $value;
                        self::singleton()->__destruct();
                }
        }

        public static function get($variable)
        {
                if (property_exists('testUser', $variable))
                {
                        return self::singleton()->{$variable};
                }
        }
        
        public static function iterateRequests()
        {
                self::singleton()->requests++;
        }

        public function __construct()
        {
                $fp = fopen('session.txt', 'a+');
                $serialized = fread($fp, 8196);
                if (!empty($serialized))
                {
                        foreach ((array) unserialize($serialized) as $key => $value)
                        {
                                $this->{$key} = $value;
                        }
                }
        }

        public function __destruct()
        {
                $fp = fopen('session.txt', 'w');
                fwrite($fp, serialize($this));
                fclose($fp);
        }

}

class SRequest
{

        const PROTOCOL = 'http';
        const ADDRESS = '127.0.0.1';
        const HEADER_DELIMITER = ' ';

        public $uri;
        public $url;
        public $authRequester;
        public $authID;
        public $authSession;
        public $type;
        public $authEnvironment;
        public $location;
        public $authHeader;
        public $params = null;
        public $rawResponse;
        public $iterateSignature;
        public $authIteration;
        public $iterationSalt;
        
        public static $types = array(
            'post',
            'get',
            'put',
            'delete',
        );

        public function __construct($type, $requester, $environment, $controller, $location)
        {

                if (!in_array($type, self::$types))
                {
                        throw Exception('Invalid request type');
                }

                $this->type = $type;
                $this->url = self::PROTOCOL . '://' . self::ADDRESS;
                $this->uri = '/' . $requester . '/' . $environment . '/' . $controller . '/' . $location;
        }

        public function setParams($params)
        {
                $this->params = $params;
        }

        public function setAuthRequester($requester)
        {
                $this->authRequester = $requester;
        }

        public function setAuthID($authID)
        {
                $this->authID = $authID;
        }

        public function setAuthSession($authSession)
        {
                $this->authSession = $authSession;
        }

        public function setAuthEnvironment($environment)
        {
                $this->authEnvironment = $environment;
        }
        
        public function setAuthIteration($iteration, $salt)
        {
                $this->iterateSignature = true;
                $this->authIteration = $iteration;
                $this->iterationSalt = $salt;
        }
        
        //if (utility::hash($this->sessionKey . $this->sessionData['validatedRequests'] . static::ITERATION_SALT) != $signature)
        public function signature()
        {
                if (isset($this->authSession))
                {
                        
                        $this->authToken = $this->uri;
                        if (!empty($this->input))
                        {
                                $this->authToken .= implode('&', $this->input);
                        }

                        $this->authToken = rtrim($this->uri, '?');
                        $this->authToken = rtrim($this->uri, '/');
                        if (!empty($this->params))
                        {
                                $this->authToken .= '?';
                                $this->authToken .= http_build_query($this->params);
                        }
                        
                        $signature = utility::hmac($this->authSession, $this->authToken);
                        
                        if (($this->authRequester == 'a' || $this->iterateSignature) && $this->authIteration && $this->iterationSalt)
                        {
                                return utility::hash($signature . $this->authIteration . $this->iterationSalt);
                        }
                        else
                        {
                                return $signature;
                        }
                        
                }
        }

        public function authHeader()
        {
                $this->authHeader || $this->authHeader = $this->authRequester . self::HEADER_DELIMITER . $this->authEnvironment . self::HEADER_DELIMITER . $this->authID . self::HEADER_DELIMITER . $this->signature();
                return $this->authHeader;
        }

        public function __toString()
        {
                $return = dispatcher::AUTHENTICATION_HEADER . ': ' . $this->authHeader() . PHP_EOL;
                $return .= $this->uri . PHP_EOL;
                $return .= print_r($this->params, true);
                $return .= PHP_EOL;
                return $return;
        }

        public function send()
        {
                $this->rawResponse = rest::{$this->type}($this->url . $this->uri, $this->params, array(CURLOPT_HTTPHEADER => array('X_AUTHORIZATION: ' . $this->authHeader())));
                $this->response = json_decode(substr($this->rawResponse, 3));
                if (!empty($this->response->body))
                {
                        $this->body = $this->response->body;
                        return $this->body;
                }
                return $this->response;
        }

        public static function post($requester, $environment, $controller, $location = null)
        {
                return new SRequest('post', $requester, $environment, $controller, $location);
        }

        public static function get($requester, $environment, $controller, $location)
        {
                return new SRequest('get', $requester, $environment, $controller, $location);
        }

        public static function put($requester, $environment, $controller, $location)
        {
                return new SRequest('put', $requester, $environment, $controller, $location);
        }

        public static function delete($requester, $environment, $controller, $location)
        {
                return new SRequest('delete', $requester, $environment, $controller, $location);
        }

}

class SRP
{

        public $client;
        public $startResponse;
        public $finishResponse;
        public $id;
        public $password;
        public $type = 'email';
        public $requester = 'c';
        public $environment = 'base';
        public $sessionKey;

        public function __construct($type, $id, $password)
        {
                $this->type = $type;
                $this->id = $id;
                $this->password = $password;
        }

        public function start($controller = 'authenticate')
        {
                require_once $_SERVER['_HTDOCS_'] . '/base_include.php';

                $this->client = srpModel::begin($this->id);
                $params = array('A' => $this->client->A);

                $location = $this->type . '/' . $this->id;
                $request = SRequest::post($this->requester, $this->environment, $controller, $location);
                $request->setParams($params);
                $this->startResponse = $request->send();
                var_dump($request);

                require_once $_SERVER['_HTDOCS_'] . '/base_classes/utility/password.php';
                $passwordHash = utility::hashPassword(passwordModel::PASSWORD_SALT . $this->password, $this->startResponse->srp->salt2);
                $this->client->password = $passwordHash;
        }
        
        public static function fromUid($uid, $password)
        {
                return new SRP('uid', $uid, $password);
        }

        public static function fromEmail($email, $password)
        {
                return new SRP('email', $email, $password);
        }

        public static function fromUserid($id, $password)
        {
                return new SRP('userid', $id, $password);
        }

        public static function fromAdminid($id, $password)
        {
                return new SRP('adminid', $id, $password);
        }

        public function generateValues()
        {
                $this->client->B = $this->startResponse->srp->B;
                $this->client->salt = $this->startResponse->srp->B;
                $this->client->salt_resource = gmp_init($this->startResponse->srp->salt, 16);
                $this->client->B_resource = gmp_init($this->client->B, 16);
                $this->client->k();
                $this->client->x();
                $this->client->u();
                $this->client->v();
                $this->client->clientSessionKey();
                $this->client->serverSessionKey = $this->client->clientSessionKey;
                $this->client->serverSessionKey_resource = gmp_init($this->client->clientSessionKey, 16);
                $this->client->clientProof();
                $this->client->serverProof();
        }

        public function finish($controller = 'session')
        {

                $this->generateValues();

                $location = $this->type . '/' . $this->id;
                $params = array('proof' => $this->client->clientProof);
                $request = SRequest::post($this->requester, $this->environment, $controller, $location);
                $request->setParams($params);
                $this->finishResponse = $request->send();
                var_dump($request);
                $this->sessionKey = $this->client->clientSessionKey;
                return true;
        }

}

class test
{

        public $args;
        public $orderedArgs;

        public function extractVariables($arguments)
        {

                if (!empty($this->orderedArgs) && count($this->orderedArgs) == count($arguments))
                {
                        foreach ($this->orderedArgs as $key => $value)
                        {
                                $this->args[$value] = $arguments[$key];
                        }
                        return;
                }

                if (count($arguments) % 2 != 0)
                {
                        die('Invalid number of arguments');
                }
                for ($c = count($arguments), $i = 0; $i < $c; $i++)
                {
                        if ($i % 2 == 0)
                        {
                                $key = $arguments[$i];
                        }
                        else
                        {
                                $this->args[$key] = $arguments[$i];
                        }
                }
                return;
        }

}
