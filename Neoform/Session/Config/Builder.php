<?php

    namespace Neoform\Session\Config;

    use Neoform;

    class Builder extends Neoform\Config\Builder {

        /**
         * @return Neoform\Locale\Config
         */
        public function build() {
            return new Neoform\Session\Config($this->configValues);
        }

        /**
         * Config default values
         *
         * @return array
         */
        protected function defaults() {
            return [
                // lifetime of a non-authenticated session
                'default_lifetime' => 3600,

                // lifetime of a non-authenticated session
                'default_flash_lifetime' => 3600,

                // how long before a ref code is considered expired
                'xsrf_ttl' => 3600,

                // random string to make the ref code more random - you can change this, but it will
                // kill all sessions (forms that are being filled out).
                'xsrf_salt' => null,

                // Flash session cache engine
                'flash_cache_engine' => null,

                // Flash session cache pool
                'flash_cache_pool_read'  => null,
                'flash_cache_pool_write' => null,
            ];
        }

        /**
         * Validate the config values
         *
         * @throws Neoform\Config\Exception
         */
        public function validate() {

            if (empty($this->configValues['xsrf_ttl'])) {
                throw new Neoform\Config\Exception('\'xsrf_ttl\' must be set');
            }

            if (empty($this->configValues['xsrf_salt'])) {
                throw new Neoform\Config\Exception('\'xsrf_salt\' must be set');
            }

            if (empty($this->configValues['flash_cache_engine'])) {
                throw new Neoform\Config\Exception('\'flash_cache_engine\' must be set');
            }

            if (empty($this->configValues['flash_cache_engine'])) {
                throw new Neoform\Config\Exception('\'flash_cache_engine\' must be set');
            }

            if (empty($this->configValues['flash_cache_pool_read'])) {
                throw new Neoform\Config\Exception('\'flash_cache_pool_read\' must be set');
            }

            if (empty($this->configValues['flash_cache_pool_write'])) {
                throw new Neoform\Config\Exception('\'flash_cache_pool_write\' must be set');
            }
        }

        /**
         * Validate the config values after the config has been compiled
         */
        public function validatePost(Neoform\Config\Collection $configs) {

        }
    }