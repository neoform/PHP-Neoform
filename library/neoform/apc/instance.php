<?php

    namespace neoform\apc;

    use neoform;

    /**
     * APC cache instance
     * to use: cache_apc::instance()
     */
    class instance extends \ArrayObject {

        use neoform\core\instance;

        protected $key_prefix;

        public function __construct(array $config) {
            $this->key_prefix = "{$config['key_prefix']}:";
        }

        /**
         * Gets a record from APC
         *
         * @param $key
         *
         * @return mixed
         * @throws neoform\apc\exception
         */
        public function __get($key) {
            $data = apc_fetch($this->key_prefix . $key, $success);
            if ($success) {
                return $data;
            } else {
                throw new neoform\apc\exception('Cache does not exist');
            }
        }

        /**
         * Gets a record from APC
         *
         * @param $key
         *
         * @return mixed
         * @throws neoform\apc\exception
         */
        public function get($key) {
            $data = apc_fetch($this->key_prefix . $key, $success);
            if ($success) {
                return $data;
            } else {
                throw new neoform\apc\exception('Cache does not exist');
            }
        }

        /**
         * Creates a record in APC
         *
         * @param string $key
         * @param string $val
         * @param int    $ttl
         *
         * @return bool
         */
        public function set($key, $val, $ttl=0) {
            return apc_store($this->key_prefix . $key, $val, $ttl);
        }

        /**
         * Delete's a record from APC
         *
         * @param $key
         *
         * @return bool|string[]
         */
        public function __unset($key) {
            return apc_delete($this->key_prefix . $key);
        }

        /**
         * Delete's a record from APC
         *
         * @param string $key
         *
         * @return bool|string[]
         */
        public function del($key) {
            return apc_delete($this->key_prefix . $key);
        }

        /**
         * Checks if a record exists
         *
         * @param string $key
         *
         * @return bool|string[]
         */
        public function __isset($key) {
            return apc_exists($this->key_prefix . $key);
        }

        /**
         * Increment the value of a record
         *
         * @param string $key
         * @param int    $step
         *
         * @return bool|int
         */
        public function increment($key, $step=1) {
            return apc_inc($this->key_prefix . $key, $step);
        }

        /**
         * Decrement the value of a record
         *
         * @param string $key
         * @param int    $step
         *
         * @return bool|int
         */
        public function decrement($key, $step=1) {
            return apc_dec($this->key_prefix . $key, $step);
        }

        /**
         * Delete all cached vars
         *
         * @return bool
         */
        public function flush() {
            return apc_clear_cache();
        }

        /**
         * Get general info from APC
         *
         * @return array|bool
         */
        public function info() {
            return apc_cache_info();
        }
    }