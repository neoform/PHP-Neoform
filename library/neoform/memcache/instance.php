<?php

    namespace neoform\memcache;

    use neoform\core;

    /**
     * Memcache Instance
     * to use: core::memcache($pool)
     */
    class instance extends \memcached {

        use core\instance;

        public function __construct($name) {
            //only maintain persistent connections for each site (key_prefix is used for this)
            $prefix = core::config()['memcache']['key_prefix'];
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