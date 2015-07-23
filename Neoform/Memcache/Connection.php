<?php

    namespace Neoform\Memcache;

    use Neoform;

    /**
     * Memcache Instance
     * to use: Core::memcache($pool)
     */
    class Connection extends \Memcached {

        /**
         * @param Config $config
         * @param string $name
         */
        public function __construct(Config $config, $name) {
            // Only maintain persistent connections for each site (key_prefix is used for this)
            $prefix = $config->getKeyPrefix();
            parent::__construct(($prefix ? "{$prefix}:" : '') . $name);
        }

        /**
         * @return bool
         */
        public function rowFound() {
            return $this->getResultCode() === parent::RES_SUCCESS;
        }
    }