<?php

class server extends test
{
        const ITERATION_SALT = 'AHDAl8zagO4mAJNH4Aukz8M2G7dF9e5CvkUMWYpl';
        public $orderedArgs;

        public function create($arguments)
        {

                $this->orderedArgs = [
                    'userid',
                    'email',
                    'firstName',
                    'lastName',
                    'password',
                ];
                $this->extractVariables($arguments);
                
                $location = 'userid/' . $this->args['userid'];
                
                $request = SRequest::post('s', 'hrs', 'server', $location);
                $request->setAuthRequester(testUser::get('type'));
                $request->setAuthID(testUser::get('id'));
                $request->setAuthSession(testUser::get('sessionKey'));
                $request->setAuthEnvironment(testUser::get('environment'));
                $request->setParams($this->args);
                $response = (array) $request->send();
                
                var_dump($request);
                var_dump($response);
                if (isset($response['meta-data']->Diagnostics->Exceptions))
                {
                        return false;
                }
                return true;
        }

        public function login($arguments)
        {

                $this->orderedArgs = [
                    'uid',
                    'slaveid',
                    'password',
                ];
                $this->extractVariables($arguments);

                require_once $_SERVER['_HTDOCS_'] . '/base_include.php';
                require_once $_SERVER['_HTDOCS_'] . '/base_classes/utility/password.php';
                
                
                // start
                $srp = SRP::fromUid($this->args['uid'], $this->args['password']);
                $srp->requester = 's';
                $srp->environment = 'hrs';
                
                $srp->client = srpModel::begin($srp->id);
                $params = array('A' => $srp->client->A);

                $location = $srp->type . '/' . $srp->id . '/slaveid/' . $this->args['slaveid'];
                $request = SRequest::post($srp->requester, $srp->environment, 'serverAuthenticate', $location);
                $request->setParams($params);
                $srp->startResponse = $request->send();
                var_dump($request);

                
                $passwordHash = utility::hashPassword(passwordModel::PASSWORD_SALT . $srp->password, $srp->startResponse->srp->salt2);
                $srp->client->password = $passwordHash;
                
                // finish
                $srp->generateValues();

                $location = $srp->type . '/' . $srp->id . '/slaveid/' . $this->args['slaveid'];
                $params = array('proof' => $srp->client->clientProof);
                $request = SRequest::post($srp->requester, $srp->environment, 'serverSession', $location);
                $request->setParams($params);
                $srp->finishResponse = $request->send();
                var_dump($request);
                $srp->sessionKey = $srp->client->clientSessionKey;
                #$srp->finish('serverSession');

                if (empty($srp->sessionKey) || empty($srp->finishResponse->server_id))
                {
                        print_r($srp->finishResponse, true);
                        return false;
                }

                testUser::set('id', $srp->finishResponse->uid);
                testUser::set('sessionKey', $srp->sessionKey);
                //testUser::set('email', $this->args['email']);
                testUser::set('type', 's');
                testUser::set('environment', 'hrs');
                return true;
        }
        
        public function loginByEmail($arguments)
        {

                $this->orderedArgs = [
                    'email',
                    'slaveid',
                    'password',
                ];
                $this->extractVariables($arguments);
                
                require_once $_SERVER['_HTDOCS_'] . '/base_include.php';
                require_once $_SERVER['_HTDOCS_'] . '/base_classes/utility/password.php';
                
                
                // start
                $srp = SRP::fromEmail($this->args['email'], $this->args['password']);
                $srp->requester = 's';
                $srp->environment = 'hrs';
                
                $srp->client = srpModel::begin($srp->id);
                $params = array('A' => $srp->client->A);

                $location = $srp->type . '/' . $srp->id . '/slaveid/' . $this->args['slaveid'];
                $request = SRequest::post($srp->requester, $srp->environment, 'serverAuthenticate', $location);
                $request->setParams($params);
                $srp->startResponse = $request->send();
                var_dump($request);

                
                $passwordHash = utility::hashPassword(passwordModel::PASSWORD_SALT . $srp->password, $srp->startResponse->srp->salt2);
                $srp->client->password = $passwordHash;
                
                // finish
                $srp->generateValues();

                $location = $srp->type . '/' . $srp->id . '/slaveid/' . $this->args['slaveid'];
                $params = array('proof' => $srp->client->clientProof);
                $request = SRequest::post($srp->requester, $srp->environment, 'serverSession', $location);
                $request->setParams($params);
                $srp->finishResponse = $request->send();
                var_dump($request);
                $srp->sessionKey = $srp->client->clientSessionKey;
                #$srp->finish('serverSession');

                if (empty($srp->sessionKey) || empty($srp->finishResponse->server_id))
                {
                        print_r($srp->finishResponse, true);
                        return false;
                }

                testUser::set('id', $srp->finishResponse->uid);
                testUser::set('sessionKey', $srp->sessionKey);
                //testUser::set('email', $this->args['email']);
                testUser::set('type', 's');
                testUser::set('environment', 'hrs');
                return true;
        }

        public function byUID($arguments)
        {

                $this->orderedArgs = [
                    'uid'
                ];
                $this->extractVariables($arguments);

                $requester = 's';
                $environment = 'hrs';
                $controller = 'server';
                $location = 'uid/' . $this->args['uid'];

                $request = SRequest::get($requester, $environment, $controller, $location);
                $request->setAuthRequester(testUser::get('type'));
                $request->setAuthID(testUser::get('id'));
                $request->setAuthSession(testUser::get('sessionKey'));
                $request->setAuthEnvironment(testUser::get('environment'));
                $response = (array) $request->send();
                
                var_dump($request);
                var_dump($response);
                if (isset($response['meta-data']->Diagnostics->Exceptions))
                {
                        return false;
                }
                return true;
        }
        
        public function byEmail($arguments)
        {

                $this->orderedArgs = [
                    'email'
                ];
                $this->extractVariables($arguments);

                $requester = 's';
                $environment = 'hrs';
                $controller = 'server';
                $location = 'email/' . $this->args['email'];

                $request = SRequest::get($requester, $environment, $controller, $location);
                $request->setAuthRequester(testUser::get('type'));
                $request->setAuthID(testUser::get('id'));
                $request->setAuthSession(testUser::get('sessionKey'));
                $request->setAuthEnvironment(testUser::get('environment'));
                $response = (array) $request->send();
                
                var_dump($request);
                var_dump($response);
                if (isset($response['meta-data']->Diagnostics->Exceptions))
                {
                        return false;
                }
                return true;
        }
        
        public function setStatus($arguments)
        {
                
                $this->orderedArgs = [
                    'uid',
                    'status',
                    'localTime',
                    'actions',
                ];
                $this->extractVariables($arguments);
                
                $requester = 's';
                $environment = 'hrs';
                $controller = 'serverStatus';
                $location = 'uid/' . $this->args['uid'];
                $params = $this->args;
                
                $request = SRequest::post($requester, $environment, $controller, $location);
                $request->setAuthRequester(testUser::get('type'));
                $request->setAuthID(testUser::get('id'));
                $request->setAuthSession(testUser::get('sessionKey'));
                $request->setAuthEnvironment(testUser::get('environment'));
                $request->setParams($params);
                $response = (array) $request->send();
                
                var_dump($request);
                var_dump($response);
                if (isset($response['meta-data']->Diagnostics->Exceptions))
                {
                        return false;
                }
                return true;
                
        }
        
        // example of edit requiring a session and ownership of object
        public function getStatus($arguments)
        {
                $this->orderedArgs = [
                    'uid',
                ];
                $this->extractVariables($arguments);

                $requester = 's';
                $environment = 'hrs';
                $controller = 'serverStatus';
                $location = 'uid/' . $this->args['uid'];

                $request = SRequest::get($requester, $environment, $controller, $location);
                $request->setAuthRequester(testUser::get('type'));
                $request->setAuthID(testUser::get('id'));
                $request->setAuthSession(testUser::get('sessionKey'));
                $request->setAuthEnvironment(testUser::get('environment'));
                $request->setParams($params);
                $response = (array) $request->send();
                
                var_dump($request);
                var_dump($response);
                if (isset($response['meta-data']->Diagnostics->Exceptions))
                {
                        return false;
                }
                return true;
        }

        // example of edit requiring them to re-enter password
        public function changePassword($arguments)
        {
                $this->orderedArgs = [
                    'uid',
                    'oldPassword',
                    'newPassword',
                ];
                $this->extractVariables($arguments);

                $requester = 's';
                $environment = 'hrs';
                $controller = 'server';

                $srp = SRP::fromUid($this->args['uid'], $this->args['oldPassword']);
                $srp->requester = 's';
                $srp->environment = 'hrs';
                $srp->start('serverAuthenticate');
                $srp->generateValues();
                
                $location = 'uid/' . $this->args['uid'];
                $params = array('action' => 'setPassword', 'proof' => $srp->client->clientProof, 'newPassword' => $this->args['newPassword']);

                $request = SRequest::put($requester, $environment, $controller, $location);
                $request->setAuthRequester(testUser::get('type'));
                $request->setAuthID(testUser::get('id'));
                $request->setAuthSession(testUser::get('sessionKey'));
                $request->setAuthEnvironment(testUser::get('environment'));
                $request->setParams($params);
                $response = (array) $request->send();
                var_dump($response);
                if (isset($response['meta-data']->Diagnostics->Exceptions))
                {
                        return false;
                }
                return true;
        }
        
}
