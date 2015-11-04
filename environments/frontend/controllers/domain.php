<?php

class domain extends controller
{

        public $baseUserModel;

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
                    'property' => 'domain',
                    'class' => 'baseUserModel',
                    'method' => 'domains',
                    'subObjectMethod' => 'domainByName',
                    'includes' => '/environments/base/models/user.php',
                ],
            ],
            'domainid' => [
                'FILTER' => FILTER_VALIDATE_FLOAT,
                'RETURN_FUNCTION' => [
                    'object_property' => 'baseUserModel',
                    'property' => 'domain',
                    'class' => 'baseUserModel',
                    'method' => 'domains',
                    'subObjectMethod' => 'domainById',
                    'includes' => '/environments/base/models/user.php',
                ],
            ],
            //class->object_property->methodName()->subObjectMethod(resource)
        ];
        public $create_data = [
            'name' => [
                'RETURN_FUNCTION' => [
                    'class' => 'valid',
                    'method' => 'string'
                ],
                'optional' => true
            ],
        ];

        public function display()
        {
                $this->runOptions(static::PRIMARY_MODEL);
                $this->output = $this->baseUserModel->domain;
        }

        public function create()
        {
                if ($this->authenticationModel instanceof baseUserModel)
                {
                        $this->createValidation($this->input);

                        require_once $_SERVER['_HTDOCS_'] . '/environments/frontend/models/domain.php';
                        $domainModel = frontendDomainModel::create($this->authenticationModel, $this->input);
                        $this->output = $domainModel;
                }
        }

        public function modify()
        {

                if (empty($this->baseUserModel->domain) || empty($this->input['action']))
                {
                        throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
                }

                $this->domain = $this->baseUserModel->domain;

                switch ($this->input['action'])
                {
                        case 'setName':
                        case 'adminSetName':

                                if (empty($this->input['name']))
                                {
                                        error::addError('No name specificed');
                                        throw new error(errorCodes::ERROR_EXPECTATION_FAILED);
                                }

                                if ($this->baseUserModel->domains()->domainByName($this->input['name']))
                                {
                                        error::addError('Domain already exists');
                                        throw new error(errorCodes::ERROR_INTERNAL_ERROR);
                                }

                                $this->domain->changeName($this->input['name']);

                                break;
                }
        }

}
