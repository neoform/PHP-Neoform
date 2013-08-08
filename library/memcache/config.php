<?php

    class memcache_config extends entity_config_defaults {

        protected function defaults() {
            return [

                //leave blank (empty string) if no prefix is needed
                //this prefix is useful if you have multiple instances of the same code on the same memcache pool (maybe prod/dev on one memcache pool)
                'key_prefix' => null,

                'default_pool' => null,

                'pools' => [],
            ];
        }

        /**
         * Validate the config values
         *
         * @throws config_exception
         */
        public function validate() {

            if (empty($this->config['default_pool'])) {
                throw new config_exception('"default_pool" must be set');
            }

            if (empty($this->config['pools']) || ! is_array($this->config['pools']) || ! count($this->config['pools'])) {
                throw new config_exception('"pools" must contain at least one server');
            }
        }
    }