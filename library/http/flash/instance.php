<?php

    /**
     * Short term data storage - useful for very short lived sessions, data is highly volatile and not guarantied to
     * exist on read. Best uses are for redirect session data.
     */
    class http_flash_instance {

        use core_instance;

        protected $session_engine;
        protected $hash;
        protected $flash_cache_engine;
        protected $flash_cache_pool_read;
        protected $flash_cache_pool_write;
        protected $default_flash_lifetime;

        public function __construct(array $config) {
            //initialize the session storage engine
            $this->hash = base64_encode(auth_lib::get_hash_cookie());

            $this->flash_cache_engine     = $config['flash_cache_engine'];
            $this->flash_cache_pool_read  = $config['flash_cache_pool_read'];
            $this->flash_cache_pool_write = $config['flash_cache_pool_write'];
            $this->default_flash_lifetime = $config['default_flash_lifetime'];
        }

        /**
         * Get a flash value by key
         *
         * @param string $key
         *
         * @return mixed
         */
        public function get($key) {
            $engine = "cache_{$this->flash_cache_engine}_driver";
            return $engine::get($this->flash_cache_pool_read, "{$this->hash}:{$key}");
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
            $engine = "cache_{$this->flash_cache_engine}_driver";
            return $engine::set(
                $this->flash_cache_pool_write,
                "{$this->hash}:{$key}",
                $val,
                $ttl !== null ? $ttl : (int) $this->default_flash_lifetime
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
            $engine = "cache_{$this->flash_cache_engine}_driver";
            return $engine::delete($this->flash_cache_pool_write, "{$this->hash}:{$key}");
        }
    }