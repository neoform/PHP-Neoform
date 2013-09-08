<?php

    namespace neoform;

    class redis_config extends entity_config_defaults {

        protected function defaults() {
            return [
                //leave black (empty string) if no prefix is needed
                //this prefix is useful if you have multiple instances of the same code base
                'key_prefix' => null,

                'default_pool_read'  => null,
                'default_pool_write' => null,

                'persistent_connection'         => false,
                'persistent_connection_timeout' => 0,
                'persistent_connection_id'      => null,

                'pools' => [],
            ];
        }

        /**
         * Validate the config values
         *
         * @throws config_exception
         */
        public function validate() {

            if (empty($this->config['default_pool_read'])) {
                throw new config_exception('"default_pool_read" must be set');
            }

            if (empty($this->config['default_pool_write'])) {
                throw new config_exception('"default_pool_write" must be set');
            }

            if (empty($this->config['pools']) || ! \is_array($this->config['pools']) || ! \count($this->config['pools'])) {
                throw new config_exception('"pools" must contain at least one server');
            }
        }
    }