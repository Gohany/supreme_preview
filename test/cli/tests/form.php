<?php

class form extends test
{
        
        public $orderedArgs;
        
        public function create($arguments)
        {
                $this->orderedArgs = [
                    'domainid',
                    'name',
                    'data',
                ];
                $this->extractVariables($arguments);

                $requester = 'c';
                $environment = 'frontend';
                $controller = 'form';

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
                    'formid'
                ];
                $this->extractVariables($arguments);

                $requester = 'c';
                $environment = 'frontend';
                $controller = 'form';
                $location = 'userid/' . $this->args['userid'] . '/formid/' . $this->args['formid'];

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
        
        public function byDomainID($arguments)
        {

                $this->orderedArgs = [
                    'userid',
                    'domainid'
                ];
                $this->extractVariables($arguments);

                $requester = 'c';
                $environment = 'frontend';
                $controller = 'form';
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
        
        public function setData($arguments)
        {
                
                $this->orderedArgs = [
                    'userid',
                    'formid',
                    'data',
                ];
                $this->extractVariables($arguments);

                $requester = 'c';
                $environment = 'frontend';
                $controller = 'form';
                $location = 'userid/' . $this->args['userid'] . '/formid/' . $this->args['formid'];
                $params = array('action' => 'setData', 'data' => $this->args['data']);

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