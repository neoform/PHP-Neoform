<?php

    namespace Neoform\Entity\Repo\Cache\Driver;

    use Neoform;

    class None implements Neoform\Entity\Repo\Cache\Driver {

        /**
         * @var None
         */
        protected static $instance;

        /**
         * @param string $enginePool
         *
         * @return None
         */
        public static function getInstance($enginePool) {
            if (! self::$instance) {
                self::$instance = new static;
            }

            return self::$instance;
        }

        /**
         * @return $this
         */
        public function batchStart() {
            return $this;
        }

        /**
         * Execute pipelined (batch) queries and return result
         */
        public function batchExecute() {

        }

        /**
         * Checks if cached record exists.
         *
         * @param string $key
         *
         * @return boolean
         */
        public function exists($key) {
            return false;
        }

        /**
         * Gets cached data.
         *  if record does exist, an array with a single element, containing the data.
         *  returns null if record does not exist
         *
         * @param string $key
         *
         * @return array|null returns null if record does not exist.
         */
        public function get($key) {
            return;
        }

        /**
         * @param string   $key
         * @param mixed    $data
         * @param int|null $ttl
         *
         * @return bool
         */
        public function set($key, $data, $ttl=null) {
            return false;
        }

        /**
         * Fetch multiple rows from redis
         *
         * @param array  $keys
         *
         * @return array
         * @throws Neoform\Entity\Repo\Exception
         */
        public function getMulti(array $keys) {
            return [];
        }

        /**
         * Set multiple records at the same time
         *
         * It is recommended that this function be wrapped in pipeline_start() and pipeline_execute();
         *
         * @param array    $rows
         * @param int|null $ttl
         *
         * @return mixed
         */
        public function setMulti(array $rows, $ttl=null) {
            return false;
        }

        /**
         * Delete a single record
         *
         * @param string $key
         *
         * @return int the number of keys deleted
         */
        public function delete($key) {
            return 0;
        }

        /**
         * Delete multiple entries from cache
         *
         * @param array $keys
         *
         * @return integer the number of keys deleted
         */
        public function deleteMulti(array $keys) {
            return 0;
        }

        /**
         * Expire a single record
         *
         * @param string  $key
         * @param integer $ttl how many seconds left for this key to live - if not set, it will expire now
         *
         * @return integer the number of keys deleted
         */
        public function expire($key, $ttl=0) {
            return 0;
        }

        /**
         * Expire multiple entries
         *
         * @param array   $keys
         * @param integer $ttl how many seconds left for this key to live - if not set, it will expire now
         *
         * @return integer the number of keys deleted
         */
        public function expireMulti(array $keys, $ttl=0) {
            return 0;
        }

        /**
         * @return bool
         */
        public function flush() {
            return true;
        }
    }