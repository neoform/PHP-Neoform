<?php

    namespace neoform\http\flash;

    use neoform;

    /**
     * Short term data storage - useful for very short lived sessions, data is highly volatile and not guarantied to
     * exist on read. Best uses are for redirect session data.
     */
    class model {

        protected $session_engine;
        protected $hash;
        protected $flash_cache_engine;
        protected $flash_cache_pool_read;
        protected $flash_cache_pool_write;
        protected $default_flash_lifetime;

        public function __construct(array $config) {
            //initialize the session storage engine
            $this->hash = base64_encode(neoform\auth\lib::get_hash_cookie());

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
            $engine = "\\neoform\\cache\\{$this->flash_cache_engine}\\driver";
            return $engine::get($this->flash_cache_pool_read, "http_flash:{$this->hash}:{$key}");
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
            $engine = "\\neoform\\cache\\{$this->flash_cache_engine}\\driver";
            return $engine::set(
                $this->flash_cache_pool_write,
                "http_flash:{$this->hash}:{$key}",
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
            $engine = "\\neoform\\cache\\{$this->flash_cache_engine}\\driver";
            return $engine::delete($this->flash_cache_pool_write, "http_flash:{$this->hash}:{$key}");
        }
    }