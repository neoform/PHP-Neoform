<?php

    namespace Neoform\Session;

    use Neoform;

    class Config extends Neoform\Config\Model {

        /**
         * Lifetime of a non-authenticated session
         *
         * @return int
         */
        public function getDefaultTtl() {
            return (int) $this->values['default_lifetime'];
        }

        /**
         * How long before a XSRF token is considered expired
         *
         * @return int
         */
        public function getXsrfTtl() {
            return (int) $this->values['xsrf_ttl'];
        }

        /**
         * Salt to make the XSRF token have more entropy
         *
         * Warning: changing this, but it will kill all active user sessions (forms that are being filled out).
         *
         * @return string
         */
        public function getXsrfSalt() {
            return $this->values['xsrf_salt'];
        }

        /**
         * lifetime of a non-authenticated flash session
         *
         * @return int
         */
        public function getDefaultFlashTtl() {
            return (int) $this->values['default_flash_lifetime'];
        }

        /**
         * Flash session cache engine
         *
         * @return string
         */
        public function getFlashCacheEngine() {
            return $this->values['flash_cache_engine'];
        }

        /**
         * Flash session cache pool
         *
         * @return string
         */
        public function getFlashCachePoolRead() {
            return $this->values['flash_cache_pool_read'];
        }

        /**
         * Flash session cache pool
         *
         * @return string
         */
        public function getFlashCachePoolWrite() {
            return $this->values['flash_cache_pool_write'];
        }
    }