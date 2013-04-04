<?php

    /**
     * Memcache Instance
     * to use: core::cache_memcache($pool)
     */
    class cache_memcache_instance extends memcached{

        use core_instance;

		public function __construct($name) {
			//only maintain persistent connections for each site (key_prefix is used for this)
			$prefix = core::config()->memcache['key_prefix'];
			parent::__construct((strlen($prefix) ? $prefix . ':' : '') . $name);
		}

		public function set($key, $val, $ttl=false) {
			if ($ttl === false) { //default becomes 1 hour
				$ttl = 3660;
			}
			return parent::set($key, $val, $ttl);
		}

		public function row_found() {
			return $this->getResultCode() === memcached::RES_SUCCESS;
		}
    }