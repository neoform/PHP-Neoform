<?php

    namespace Neoform\Entity\Repo\Cache;

    /**
     * Driver interface for cache classes
     */
    interface Driver {

        /**
         * Activate a pipelined (batch) query
         *
         * @return $this
         */
        public function batchStart();

        /**
         * Execute pipelined (batch) queries and return result
         *
         * @return array result of batch operation
         */
        public function batchExecute();

        /**
         * Checks if a record exists
         *
         * @param string $key
         *
         * @return bool
         */
        public function exists($key);

        /**
         * Get a record
         *
         * @param string $key
         *
         * @return array|null - if record exists it's wrapped in an array
         */
        public function get($key);

        /**
         * Get multiple records
         *
         * @param array $keys
         *
         * @return array
         */
        public function getMulti(array $keys);

        /**
         * Set a record
         *
         * @param string   $key
         * @param mixed    $data
         * @param int|null $ttl
         *
         * @return int number of records set
         */
        public function set($key, $data, $ttl=null);

        /**
         * Set multiple records
         *
         * @param array    $rows
         * @param int|null $ttl
         *
         * @return int number of records set
         */
        public function setMulti(array $rows, $ttl=null);

        /**
         * Delete a record
         *
         * @param string $key
         *
         * @return int number of records deleted
         */
        public function delete($key);

        /**
         * Delete multiple records
         *
         * @param array $keys
         *
         * @return int number of records deleted
         */
        public function deleteMulti(array $keys);

        /**
         * Expire a record
         *
         * @param string $key
         * @param int    $ttl
         *
         * @return int number of records expired
         */
        public function expire($key, $ttl=0);

        /**
         * Expire multiple records
         *
         * @param array $keys
         * @param int   $ttl
         *
         * @return int number of records expired
         */
        public function expireMulti(array $keys, $ttl=0);

        /**
         * Delete all records
         *
         * @return bool
         */
        public function flush();
    }