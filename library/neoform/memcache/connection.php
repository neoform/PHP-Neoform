<?php

    namespace neoform\memcache;

    use neoform\config;

    /**
     * Memcache Instance
     * to use: core::memcache($pool)
     */
    class connection extends \memcached {

        public function __construct($name) {
            //only maintain persistent connections for each site (key_prefix is used for this)
            $prefix = config::instance()['memcache']['key_prefix'];
            parent::__construct(($prefix ? "{$prefix}:" : '') . $name);
        }

        public function set($key, $val, $ttl=false) {
            if ($ttl === false) { //default becomes 1 hour
                $ttl = 3660;
            }
            return parent::set($key, $val, $ttl);
        }

        public function row_found() {
            return $this->getResultCode() === parent::RES_SUCCESS;
        }
    }