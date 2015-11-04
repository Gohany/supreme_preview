<?php

class stringTables
{
        
        const DB = 'stringTables';
        const DB_KEY = 'db1';
        const CACHE_EXPIRATION = 900;
        
        const DATASET_EMAIL = 'stringTableEmail';
        const DATASET_USERNAME = 'stringTableUsername';
        const CACHE_EMAIL_PREFIX = 'stringTable:email';
        const CACHE_USERNAME_PREFIX = 'stringTable:username';
        
        public static $stringTables = array(
            'email',
        );
        
        public $dataset;
        public $cacheKey;
        public $data;
        public $environment;
        public $model;
        
        public function __construct($type, $environment, $model, $value)
        {
                
                $this->model = $model;
                $this->environment = $environment;
                switch ($type)
                {
                        case 'email':
                                $this->dataset = self::DATASET_EMAIL;
                                $this->cacheKey = self::CACHE_EMAIL_PREFIX  . ':' . $environment . ':' . $model . ':' . $value;
                                $this->type = $type;
                                break;
                        case 'username':
                                $this->dataset = self::DATASET_USERNAME;
                                $this->cacheKey = self::CACHE_USERNAME_PREFIX . ':' . $environment . ':' . $model . ':' . $value;
                                $this->type = $type;
                                break;
                        default:
                                throw new error(errorCodes::ERROR_DATABASE_NOT_FOUND);
                }
                
                $cacheEntry = new cacheEntry($this->cacheKey);
                if (!$cacheEntry->get())
                {
                        $this->data = dataEngine::read($this->dataset, array('database' => self::DB, 'dbkey' => self::DB_KEY, 'data' => array(
                            $this->type => strtolower($value), 
                            'environment' => $this->environment,
                            'model' => $this->model,
                        )));
                        $cacheEntry->value = serialize($this->data);
                        $cacheEntry->expiration = self::CACHE_EXPIRATION;
                        $cacheEntry->set();
                }
                else
                {
                        $this->data = unserialize($cacheEntry->value);
                }
                
        }
        
        public static function updateStringTable($data)
        {
                $update = array_merge($data, array($this->type => strtolower($value), 'environment' => $this->environment, 'model' => $this->model));
                dataEngine::write($dataset, array('database' => self::DB, 'dbkey' => self::DB_KEY, 'action' => 'update', 'data' => $update));
                $cacheEntry = new cacheEntry($this->cacheKey, serialize($data), self::CACHE_EXPIRATION);
        }
        
}