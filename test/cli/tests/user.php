<?php

class user extends test
{

        public $orderedArgs;

        public function create($arguments)
        {

                $this->orderedArgs = [
                    'email',
                    'firstName',
                    'lastName',
                    'password',
                ];
                $this->extractVariables($arguments);

                $request = SRequest::post('c', 'base', 'user');
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
                    'email',
                    'password',
                ];
                $this->extractVariables($arguments);

                $srp = SRP::fromEmail($this->args['email'], $this->args['password']);
                $srp->start();
                $srp->finish();

                if (empty($srp->sessionKey) || empty($srp->finishResponse->user_id))
                {
                        print_r($srp->finishResponse, true);
                        return false;
                }

                testUser::set('id', $srp->finishResponse->user_id);
                testUser::set('sessionKey', $srp->sessionKey);
                testUser::set('email', $this->args['email']);
                testUser::set('type', 'c');
                testUser::set('environment', 'base');
                return true;
        }

        public function byID($arguments)
        {

                $this->orderedArgs = [
                    'userid'
                ];
                $this->extractVariables($arguments);

                $requester = 'c';
                $environment = 'base';
                $controller = 'user';
                $location = 'userid/' . $this->args['userid'];

                $request = SRequest::get($requester, $environment, $controller, $location);
                $request->setAuthRequester(testUser::get('type'));
                $request->setAuthID(testUser::get('id'));
                $request->setAuthSession(testUser::get('sessionKey'));
                $request->setAuthEnvironment(testUser::get('environment'));
                $response = (array) $request->send();
                
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
                    'userid',
                    'email',
                ];
                $this->extractVariables($arguments);

                $requester = 'c';
                $environment = 'base';
                $controller = 'user';
                $location = 'userid/' . $this->args['userid'];
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
                    'userid',
                    'oldPassword',
                    'newPassword',
                ];
                $this->extractVariables($arguments);

                $requester = 'c';
                $environment = 'base';
                $controller = 'user';

                $srp = SRP::fromUserid($this->args['userid'], $this->args['oldPassword']);
                $srp->start();
                $srp->generateValues();

                $location = 'userid/' . $srp->startResponse->user_id;
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
