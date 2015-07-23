<?php

    namespace Neoform\Entity\Repo\Cache\Driver;

    use Neoform;

    /**
     * Cache variables in memory
     */
    class Memory implements Neoform\Entity\Repo\Cache\Driver {

        /**
         * @var array holds the cache
         */
        private $vals = [];

        /**
         * @var Memory
         */
        protected static $instances = [];

        /**
         * @param string $enginePool
         *
         * @return Memory
         */
        public static function getInstance($enginePool) {
            if (! isset(self::$instances[$enginePool])) {
                self::$instances[$enginePool] = new static;
            }

            return self::$instances[$enginePool];
        }

        /**
         * Dummy function since there's no need to batch when using local memory
         *
         * @return $this
         */
        public function batchStart() {
            return $this;
        }

        /**
         * Dummy function since there's no need to batch when using local memory
         *
         * @return array
         */
        public function batchExecute() {
            return [];
        }

        /**
         * Checks to see if a key exists in cache
         *
         * @param string $key
         *
         * @return bool
         */
        public function exists($key) {
            return array_key_exists($key, $this->vals);
        }

        /**
         * Delete record
         *
         * @param string $key
         *
         * @return int number of records deleted
         */
        public function delete($key){
            if (array_key_exists($key, $this->vals)) {
                unset($this->vals[$key]);
                return 1;
            }

            return 0;
        }

        /**
         * @param array $keys
         *
         * @return int number of records deleted
         */
        public function deleteMulti(array $keys){
            if ($keys) {
                $i = 0;
                foreach ($keys as $key) {
                    if (array_key_exists($key, $this->vals)) {
                        unset($this->vals[$key]);
                        $i++;
                    }
                }
                return $i;
            }
            return 0;
        }

        /**
         * Get record from memory
         *
         * @param string $key
         *
         * @return array|null - if record exists it's wrapped in an array
         */
        public function get($key) {
            if (array_key_exists($key, $this->vals)) {
                return [ $this->vals[$key], ];
            }
        }

        /**
         * Set record in memory
         *
         * @param string       $key
         * @param string       $data
         * @param integer|null $ttl
         *
         * @return int number of records set
         * @throws Neoform\Entity\Repo\Exception
         */
        public function set($key, $data, $ttl=null) {
            if ($ttl !== null) {
                throw new Neoform\Entity\Repo\Exception('Memory does not support expiring sets');
            }

            $this->vals[$key] = $data;
            return 1;
        }

        /**
         * Get multiple records from memory
         *
         * @param array  $keys
         *
         * @return array
         */
        public function getMulti(array $keys) {
            $matches = [];
            foreach ($keys as $index => $key) {
                if (array_key_exists($key, $this->vals)) {
                    $matches[$index] = $this->vals[$key];
                }
            }

            return $matches;
        }

        /**
         * @param array $rows
         * @param int|null $ttl
         *
         * @return int number of records set
         */
        public function setMulti(array $rows, $ttl=null) {
            foreach ($rows as $k => $v) {
                $this->vals[$k] = $v;
            }

            return count($rows);
        }

        /**
         * @param string $key
         * @param int $ttl
         *
         * @return int number of records expired
         */
        public function expire($key, $ttl=0) {
            // This is the closest you can do with memory without it being ridiculous
            return $this->delete($key);
        }

        /**
         * @param array $keys
         * @param int $ttl
         *
         * @return int number of records expired
         */
        public function expireMulti(array $keys, $ttl=0) {
            // This is the closest you can do with memory without it being ridiculous
            return $this->deleteMulti($keys);
        }

        /**
         * Delete all values
         *
         * @return bool
         */
        public function flush() {
            $this->vals = [];
            return true;
        }
    }