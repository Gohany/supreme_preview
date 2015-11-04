<?php

class dbKeys
{

        const DATASET_FRONTEND = 'frontend';
        const DATASET_BROADCAST = 'broadcast';
        const DATASET_BASE = 'base';
        const DATASET_UNKNOWN = 'base';
        const CACHE_KEY = 'dbMap';
        const CACHE_EXPIRATION = 86400;

        protected $dbKeys = array();
        protected $dbMap;
        protected static $instance;

        public function __construct()
        {
                if (!$this->getDBMapFromCache())
                {
                        if (!$this->getDBMapFromFile())
                        {
                                error::addError('Failed to read DBMap file.');
                                throw new error(errorCodes::ERROR_INTERNAL_ERROR);
                        }
                        $this->setDBMapCache();
                }
        }

        private function getDBMapFromFile()
        {
                $file = $_SERVER['_HTDOCS_'] . '/configs/database/dbMap.json';
                if (!is_file($file) || !$dbMap = json_decode(file_get_contents($file), true))
                {
                        return false;
                }

                $this->dbMap = $dbMap;

                return $this->dbMap;
        }

        private function getDBMapFromCache()
        {
                $cacheEntry = new cacheEntry(self::CACHE_KEY);
                if (!$cacheEntry->get())
                {
                        return false;
                }

                $this->dbMap = unserialize($cacheEntry->value);

                return $this->dbMap;
        }

        private function setDBMapCache()
        {
                $cacheEntry = new cacheEntry(self::CACHE_KEY, serialize($this->dbMap), self::CACHE_EXPIRATION);
                return $cacheEntry->set();
        }

        public static function setDBKey($type, $database, $key)
        {
                self::singleton()->dbKeys[$type][$database] = $key;
        }

        public static function keyFromDatabase($type, $database)
        {
                if (!isset(self::singleton()->dbMap[$database][$type]['key']))
                {
                        return false;
                }
                return self::singleton()->dbMap[$database][$type]['key'];
        }

        public static function singleton()
        {
                self::$instance || self::$instance = new dbKeys;
                return self::$instance;
        }

}