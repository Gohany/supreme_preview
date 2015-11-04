<?php

class form extends controller
{

        public $baseUserModel;
        public $domain;

        const PRIMARY_MODEL = 'baseUserModel';

        public $properties = [
            'userid' => [
                'FILTER' => FILTER_VALIDATE_FLOAT,
                'RETURN_FUNCTION' => [
                    'property' => 'baseUserModel',
                    'class' => 'baseUserModel',
                    'method' => 'fromUser_id',
                    'includes' => '/environments/base/models/user.php',
                ],
            ],
            'name' => [
                'FILTER' => FILTER_SANITIZE_STRING,
                'RETURN_FUNCTION' => [
                    'object_property' => 'baseUserModel',
                    'property' => 'form',
                    'class' => 'baseUserModel',
                    'method' => 'forms',
                    'subObjectMethod' => 'formByName',
                    'includes' => '/environments/base/models/user.php',
                ],
            ],
            'formid' => [
                'FILTER' => FILTER_VALIDATE_FLOAT,
                'RETURN_FUNCTION' => [
                    'object_property' => 'baseUserModel',
                    'property' => 'form',
                    'class' => 'baseUserModel',
                    'method' => 'forms',
                    'subObjectMethod' => 'formById',
                    'includes' => '/environments/base/models/user.php',
                ],
            ],
            'domainid' => [
                'FILTER' => FILTER_VALIDATE_FLOAT,
                'RETURN_FUNCTION' => [
                    'object_property' => 'baseUserModel',
                    'property' => 'form',
                    'class' => 'baseUserModel',
                    'method' => 'forms',
                    'subObjectMethod' => 'formsByDomain_id',
                    'includes' => '/environments/base/models/user.php',
                ],
            ],
            //class->object_property->property = class->object_property->methodName()->subObjectMethod(resource)
        ];
        public $create_data = [
            'name' => [
                'RETURN_FUNCTION' => [
                    'class' => 'valid',
                    'method' => 'string'
                ],
                'optional' => true
            ],
            'domainid' => [
                'RETURN_FUNCTION' => [
                    'class' => 'valid',
                    'method' => 'increment'
                ],
            ],
        ];

        public function display()
        {
                $this->runOptions(static::PRIMARY_MODEL);
                if (isset($this->baseUserModel->form))
                {
                        $this->output = $this->baseUserModel->form;
                }
                elseif (isset($this->baseUserModel->forms))
                {
                        $this->output = $this->baseUserModel->forms;
                }
        }

        public function create()
        {
                
                if ($this->authenticationModel instanceof baseUserModel)
                {
                        
                        $this->createValidation($this->input);
                        
                        require_once $_SERVER['_HTDOCS_'] . '/environments/frontend/models/form.php';
                        
                        $formModel = frontendFormModel::create($this->authenticationModel, $this->input);
                        $this->output = $formModel;
                }

        }
        
        public function modify()
        {

                if (empty($this->baseUserModel->form) || empty($this->input['action']))
                {
                        throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
                }

                $this->form = $this->baseUserModel->form;

                switch ($this->input['action'])
                {
                        case 'setData':
                        case 'adminSetData':

                                if (empty($this->input['data']))
                                {
                                        error::addError('No data specificed');
                                        throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
                                }

                                $this->form->setData($this->input['data']);

                                break;
                }
                
                $this->output = $this->form;
        }

}
