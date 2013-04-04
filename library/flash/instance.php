<?php

    /**
     * Short term data storage - useful for very short lived sessions, data is highly volatile and not guarantied to
     * exist on read. Best uses are for redirect session data.
     */
    class flash_instance {

        use core_instance;

        protected $session_engine;
        protected $hash;

        public function __construct() {
            //initialize the session storage engine
            $this->hash = auth_lib::get_hash_cookie();
        }

        /**
         * Get a flash value by key
         *
         * @param $key
         *
         * @return mixed
         */
        public function get($key) {
            return core::cache_memcache(core::config()->session['memcache_pool'])->get($this->hash . ':' . $key);
        }

        /**
         * Set a value in flash
         *
         * @param string       $key
         * @param string       $val
         * @param integer|null $ttl
         *
         * @return mixed
         */
        public function set($key, $val, $ttl=null) {
            return core::cache_memcache(core::config()->session['memcache_pool'])->set(
                $this->hash . ':' . $key,
                $val,
                $ttl !== null ? $ttl : (int) core::config()->session['default_flash_lifetime']
            );
        }

        /**
         * Delete a value from flash
         *
         * @param $key
         *
         * @return mixed
         */
        public function del($key) {
            return core::cache_memcache(core::config()->session['memcache_pool'])->delete($this->hash . ':' . $key);
        }
    }