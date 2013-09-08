<?php

    namespace neoform;

    class sql_config extends entity_config_defaults {

        protected function defaults() {
            return [
                // SQL charset (encoding)
                'encoding' => 'utf8',

                // the connection name that is use when all else fails to [required]
                'default_pool_read'  => null,
                'default_pool_write' => null,

                // Server pools
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