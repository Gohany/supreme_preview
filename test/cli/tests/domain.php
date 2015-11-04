<?php

class domain extends test
{
        
        public $orderedArgs;
        
        public function create($arguments)
        {
                $this->orderedArgs = [
                    'name',
                ];
                $this->extractVariables($arguments);

                $requester = 'c';
                $environment = 'frontend';
                $controller = 'domain';

                $request = SRequest::post($requester, $environment, $controller);
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
        
        public function byID($arguments)
        {

                $this->orderedArgs = [
                    'userid',
                    'domainid'
                ];
                $this->extractVariables($arguments);

                $requester = 'c';
                $environment = 'frontend';
                $controller = 'domain';
                $location = 'userid/' . $this->args['userid'] . '/domainid/' . $this->args['domainid'];

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
        
        public function byName($arguments)
        {

                $this->orderedArgs = [
                    'userid',
                    'name'
                ];
                $this->extractVariables($arguments);

                $requester = 'c';
                $environment = 'frontend';
                $controller = 'domain';
                $location = 'userid/' . $this->args['userid'] . '/name/' . $this->args['name'];

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
        
        public function changeDomainName($arguments)
        {
                $this->orderedArgs = [
                    'userid',
                    'domainid',
                    'name',
                ];
                $this->extractVariables($arguments);

                $requester = 'c';
                $environment = 'frontend';
                $controller = 'domain';
                $location = 'userid/' . $this->args['userid'] . '/domainid/' . $this->args['domainid'];
                $params = array('action' => 'setName', 'name' => $this->args['name']);

                $request = SRequest::put($requester, $environment, $controller, $location);
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
        
}