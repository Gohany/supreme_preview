<?php

class account extends test
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
                
                $request = SRequest::post('ac', 'hrs', 'account', $location);
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
                    'password',
                ];
                $this->extractVariables($arguments);

                $srp = SRP::fromUid($this->args['uid'], $this->args['password']);
                $srp->requester = 'ac';
                $srp->environment = 'hrs';
                $srp->start();
                $srp->finish();

                if (empty($srp->sessionKey) || empty($srp->finishResponse->account_id))
                {
                        print_r($srp->finishResponse, true);
                        return false;
                }

                testUser::set('id', $srp->finishResponse->uid);
                testUser::set('sessionKey', $srp->sessionKey);
                //testUser::set('email', $this->args['email']);
                testUser::set('type', 'ac');
                testUser::set('environment', 'hrs');
                return true;
        }
        
        public function loginByEmail($arguments)
        {

                $this->orderedArgs = [
                    'email',
                    'password',
                ];
                $this->extractVariables($arguments);

                $srp = SRP::fromEmail($this->args['email'], $this->args['password']);
                $srp->requester = 'ac';
                $srp->environment = 'hrs';
                $srp->start();
                $srp->finish();

                if (empty($srp->sessionKey) || empty($srp->finishResponse->account_id))
                {
                        print_r($srp->finishResponse, true);
                        return false;
                }

                testUser::set('id', $srp->finishResponse->uid);
                testUser::set('sessionKey', $srp->sessionKey);
                //testUser::set('email', $this->args['email']);
                testUser::set('type', 'ac');
                testUser::set('environment', 'hrs');
                return true;
        }

        public function byUID($arguments)
        {

                $this->orderedArgs = [
                    'uid'
                ];
                $this->extractVariables($arguments);

                $requester = 'ac';
                $environment = 'hrs';
                $controller = 'account';
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

                $requester = 'ac';
                $environment = 'hrs';
                $controller = 'account';
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

        // example of edit requiring a session and ownership of object
        public function changeEmail($arguments)
        {
                $this->orderedArgs = [
                    'uid',
                    'email',
                ];
                $this->extractVariables($arguments);

                $requester = 'c';
                $environment = 'hrs';
                $controller = 'account';
                $location = 'uid/' . $this->args['uid'];
                $params = array('action' => 'setEmail', 'email' => $this->args['email']);

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

        // example of edit requiring them to re-enter password
        public function changePassword($arguments)
        {
                $this->orderedArgs = [
                    'uid',
                    'oldPassword',
                    'newPassword',
                ];
                $this->extractVariables($arguments);

                $requester = 'c';
                $environment = 'hrs';
                $controller = 'account';

                $srp = SRP::fromUid($this->args['uid'], $this->args['oldPassword']);
                $srp->requester = 's';
                $srp->environment = 'hrs';
                $srp->start();
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
        
        public function givePermission($arguments)
        {

                $this->orderedArgs = [
                    'uid',
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
                
                $requester = 'ac';
                $environment = 'hrs';
                $controller = 'account';
                $location = 'uid/' . $this->args['uid'];
                $params = array('action' => 'setAccountPermission', 'permissionEnvironment' => $this->args['environment'], 'permissionController' => $this->args['controller'], 'permissionAction' => $this->args['action'], 'permissionModifyAction' => $this->args['modifyAction']);

                $request = SRequest::put($requester, $environment, $controller, $location);
                $request->setAuthRequester(testUser::get('type'));
                $request->setAuthID(testUser::get('id'));
                $request->setAuthSession(testUser::get('sessionKey'));
                $request->setAuthEnvironment(testUser::get('environment'));
                #$request->setAuthIteration(testUser::get('requests'), self::ITERATION_SALT);
                $request->setParams($params);
                $response = (array) $request->send();
                var_dump($request);
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
                    'accountid',
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
                
                $requester = 'ac';
                $environment = 'hrs';
                $controller = 'account';
                $location = 'accountid/' . $this->args['accountid'];
                $params = array('action' => 'removeAccountPermission', 'permissionEnvironment' => $this->args['environment'], 'permissionController' => $this->args['controller'], 'permissionAction' => $this->args['action'], 'permissionModifyAction' => $this->args['modifyAction']);

                $request = SRequest::put($requester, $environment, $controller, $location);
                $request->setParams($params);
                var_dump($request);
                $request->setAuthRequester(testUser::get('type'));
                $request->setAuthID(testUser::get('id'));
                $request->setAuthSession(testUser::get('sessionKey'));
                $request->setAuthEnvironment(testUser::get('environment'));
                #$request->setAuthIteration(testUser::get('requests'), self::ITERATION_SALT);
                $response = (array) $request->send();
                
                var_dump($response);
                if (isset($response['meta-data']->Diagnostics->Exceptions))
                {
                        return false;
                }
                testUser::iterateRequests();
                return true;
        }
        
}
