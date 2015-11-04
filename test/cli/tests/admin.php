<?php

class admin extends test
{

        public $orderedArgs;
        const ITERATION_SALT = 'BITao0rndml62u4MHS0gYQT9OpiOHtsYybixo6SK';

        public function create($arguments)
        {

                $this->orderedArgs = [
                    'email',
                    'password',
                ];
                $this->extractVariables($arguments);

                $request = SRequest::post('a', 'base', 'admin');
                $request->setAuthRequester(testUser::get('type'));
                $request->setAuthID(testUser::get('id'));
                $request->setAuthSession(testUser::get('sessionKey'));
                $request->setAuthEnvironment(testUser::get('environment'));
                $request->setAuthIteration(testUser::get('requests'), self::ITERATION_SALT);
                $request->setParams($this->args);
                $response = (array) $request->send();
                
                var_dump($response);
                if (isset($response['meta-data']->Diagnostics->Exceptions))
                {
                        return false;
                }
                testUser::iterateRequests();
                return true;
        }
        
        public function al()
        {
                return $this->login(array('email' => 'gohany@gmail.com', 'password' => 'test'));
        }
        
        public function login($arguments)
        {

                $this->orderedArgs = [
                    'email',
                    'password',
                ];
                $this->extractVariables($arguments);

                $srp = SRP::fromEmail($this->args['email'], $this->args['password']);
                $srp->requester = 'a';
                $srp->start('adminAuthenticate');
                $srp->finish('adminSession');
                
                if (empty($srp->sessionKey) || empty($srp->finishResponse->admin_id))
                {
                        return print_r($srp->finishResponse, true);
                }

                testUser::set('id', $srp->finishResponse->admin_id);
                testUser::set('sessionKey', $srp->sessionKey);
                testUser::set('email', $this->args['email']);
                testUser::set('type', 'a');
                testUser::set('environment', 'base');
                testUser::set('requests', 1);
                return true;
        }

        public function givePermission($arguments)
        {

                $this->orderedArgs = [
                    'adminid',
                    'environment',
                    'controller',
                    'action',
                    'modifyAction',
                ];
                $this->extractVariables($arguments);

                if (empty($this->args['modifyAction']))
                {
                        $this->args['modifyAction'] = null;
                }
                
                $requester = 'a';
                $environment = 'base';
                $controller = 'admin';
                $location = 'adminid/' . $this->args['adminid'];
                $params = array('action' => 'adminSetPermission', 'permissionEnvironment' => $this->args['environment'], 'permissionController' => $this->args['controller'], 'permissionAction' => $this->args['action'], 'permissionModifyAction' => $this->args['modifyAction']);

                $request = SRequest::put($requester, $environment, $controller, $location);
                $request->setAuthRequester(testUser::get('type'));
                $request->setAuthID(testUser::get('id'));
                $request->setAuthSession(testUser::get('sessionKey'));
                $request->setAuthEnvironment(testUser::get('environment'));
                $request->setAuthIteration(testUser::get('requests'), self::ITERATION_SALT);
                $request->setParams($params);
                $response = (array) $request->send();
                var_dump($response);
                if (isset($response['meta-data']->Diagnostics->Exceptions))
                {
                        return false;
                }
                testUser::iterateRequests();
                return true;
        }
        
        public function removePermission($arguments)
        {

                $this->orderedArgs = [
                    'adminid',
                    'environment',
                    'controller',
                    'action',
                    'modifyAction',
                ];
                $this->extractVariables($arguments);

                if (empty($this->args['modifyAction']))
                {
                        $this->args['modifyAction'] = null;
                }
                
                $requester = 'a';
                $environment = 'base';
                $controller = 'admin';
                $location = 'adminid/' . $this->args['adminid'];
                $params = array('action' => 'adminRemovePermission', 'permissionEnvironment' => $this->args['environment'], 'permissionController' => $this->args['controller'], 'permissionAction' => $this->args['action'], 'permissionModifyAction' => $this->args['modifyAction']);

                $request = SRequest::put($requester, $environment, $controller, $location);
                $request->setParams($params);
                var_dump($request);
                $request->setAuthRequester(testUser::get('type'));
                $request->setAuthID(testUser::get('id'));
                $request->setAuthSession(testUser::get('sessionKey'));
                $request->setAuthEnvironment(testUser::get('environment'));
                $request->setAuthIteration(testUser::get('requests'), self::ITERATION_SALT);
                $response = (array) $request->send();
                
                var_dump($response);
                if (isset($response['meta-data']->Diagnostics->Exceptions))
                {
                        return false;
                }
                testUser::iterateRequests();
                return true;
        }

        public function byID($arguments)
        {

                $this->orderedArgs = [
                    'adminid'
                ];
                $this->extractVariables($arguments);

                $requester = 'a';
                $environment = 'base';
                $controller = 'admin';
                $location = 'adminid/' . $this->args['adminid'];

                $request = SRequest::get($requester, $environment, $controller, $location);
                $request->setAuthRequester(testUser::get('type'));
                $request->setAuthID(testUser::get('id'));
                $request->setAuthSession(testUser::get('sessionKey'));
                $request->setAuthEnvironment(testUser::get('environment'));
                $request->setAuthIteration(testUser::get('requests'), self::ITERATION_SALT);
                $response = (array) $request->send();
                
                var_dump($response);
                if (isset($response['meta-data']->Diagnostics->Exceptions))
                {
                        return false;
                }
                testUser::iterateRequests();
                return true;
        }

        // example of edit requiring a session and ownership of object
        public function changeEmail($arguments)
        {
                $this->orderedArgs = [
                    'adminid',
                    'email',
                ];
                $this->extractVariables($arguments);

                $requester = 'a';
                $environment = 'base';
                $controller = 'admin';
                $location = 'adminid/' . $this->args['adminid'];
                $params = array('action' => 'setEmail', 'email' => $this->args['email']);

                $request = SRequest::put($requester, $environment, $controller, $location);
                $request->setAuthRequester(testUser::get('type'));
                $request->setAuthID(testUser::get('id'));
                $request->setAuthSession(testUser::get('sessionKey'));
                $request->setAuthEnvironment(testUser::get('environment'));
                $request->setAuthIteration(testUser::get('requests'), self::ITERATION_SALT);
                $request->setParams($params);
                $response = (array) $request->send();
                var_dump($response);
                if (isset($response['meta-data']->Diagnostics->Exceptions))
                {
                        return false;
                }
                testUser::iterateRequests();
                return true;
        }

        // example of edit requiring them to re-enter password
        public function changePassword($arguments)
        {
                $this->orderedArgs = [
                    'adminid',
                    'oldPassword',
                    'newPassword',
                ];
                $this->extractVariables($arguments);

                $requester = 'a';
                $environment = 'base';
                $controller = 'admin';

                $srp = SRP::fromAdminid($this->args['adminid'], $this->args['oldPassword']);
                $srp->start('adminAuthenticate');
                $srp->generateValues();

                $location = 'adminid/' . $srp->startResponse->admin_id;
                $params = array('action' => 'setPassword', 'proof' => $srp->client->clientProof, 'newPassword' => $this->args['newPassword']);

                $request = SRequest::put($requester, $environment, $controller, $location);
                $request->setAuthRequester(testUser::get('type'));
                $request->setAuthID(testUser::get('id'));
                $request->setAuthSession(testUser::get('sessionKey'));
                $request->setAuthEnvironment(testUser::get('environment'));
                $request->setAuthIteration(testUser::get('requests'), self::ITERATION_SALT);
                $request->setParams($params);
                $response = (array) $request->send();
                var_dump($response);
                if (isset($response['meta-data']->Diagnostics->Exceptions))
                {
                        return false;
                }
                testUser::iterateRequests();
                return true;
        }
        
        public function setUserEmail($arguments)
        {
                
                $this->orderedArgs = [
                    'userid',
                    'email',
                ];
                $this->extractVariables($arguments);
                
                $requester = 'c';
                $environment = 'base';
                $controller = 'user';
                $location = 'userid/' . $this->args['userid'];
                $params = array('action' => 'adminSetEmail', 'newEmail' => $this->args['email']);
                
                $request = SRequest::put($requester, $environment, $controller, $location);
                $request->setAuthRequester(testUser::get('type'));
                $request->setAuthID(testUser::get('id'));
                $request->setAuthSession(testUser::get('sessionKey'));
                $request->setAuthEnvironment(testUser::get('environment'));
                $request->setAuthIteration(testUser::get('requests'), self::ITERATION_SALT);
                $request->setParams($params);
                $response = (array) $request->send();
                var_dump($response);
                if (isset($response['meta-data']->Diagnostics->Exceptions))
                {
                        return false;
                }
                testUser::iterateRequests();
                return true;
                
        }
        
        public function setUserPassword($arguments)
        {
                
                $this->orderedArgs = [
                    'userid',
                    'password',
                ];
                $this->extractVariables($arguments);
                
                $requester = 'c';
                $environment = 'base';
                $controller = 'user';
                $location = 'userid/' . $this->args['userid'];
                $params = array('action' => 'adminSetPassword', 'newPassword' => $this->args['password']);
                
                $request = SRequest::put($requester, $environment, $controller, $location);
                $request->setAuthRequester(testUser::get('type'));
                $request->setAuthID(testUser::get('id'));
                $request->setAuthSession(testUser::get('sessionKey'));
                $request->setAuthEnvironment(testUser::get('environment'));
                $request->setAuthIteration(testUser::get('requests'), self::ITERATION_SALT);
                $request->setParams($params);
                $response = (array) $request->send();
                var_dump($request);
                var_dump($response);
                if (isset($response['meta-data']->Diagnostics->Exceptions))
                {
                        var_dump($request);
                        return false;
                }
                testUser::iterateRequests();
                return true;
                
        }

}
