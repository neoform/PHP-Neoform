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
            $this->hash = base64_encode(auth_lib::get_hash_cookie());
        }

        /**
         * Get a flash value by key
         *
         * @param string $key
         *
         * @return mixed
         */
        public function get($key) {
            $config = core::config()['http']['session'];
            $engine = "cache_{$config['flash_cache_engine']}_driver";
            return $engine::get("{$this->hash}:{$key}", $config['flash_cache_pool_read']);
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
            $config = core::config()['http']['session'];
            $engine = "cache_{$config['flash_cache_engine']}_driver";
            return $engine::set(
                "{$this->hash}:{$key}",
                $config['flash_cache_pool_write'],
                $val,
                $ttl !== null ? $ttl : (int) $config['default_flash_lifetime']
            );
        }

        /**
         * Delete a value from flash
         *
         * @param string $key
         *
         * @return mixed
         */
        public function del($key) {
            $config = core::config()['http']['session'];
            $engine = "cache_{$config['flash_cache_engine']}_driver";
            return $engine::delete("{$this->hash}:{$key}", $config['flash_cache_pool_write']);
        }
    }