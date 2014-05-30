<?php

    namespace neoform\redis;

    use neoform;

    class config extends neoform\config\model {

        /**
         * The array key this config file uses in the compiled configs
         *
         * @return string
         */
        public function get_array_key() {
            return 'redis';
        }

        /**
         * Config default values
         *
         * @return array
         */
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
         * @throws neoform\config\exception
         */
        public function validate() {

            if (empty($this->config['default_pool_read'])) {
                throw new neoform\config\exception('"default_pool_read" must be set');
            }

            if (empty($this->config['default_pool_write'])) {
                throw new neoform\config\exception('"default_pool_write" must be set');
            }

            if (empty($this->config['pools']) || ! is_array($this->config['pools']) || ! $this->config['pools']) {
                throw new neoform\config\exception('"pools" must contain at least one server');
            }
        }

        /**
         * Validate the config values after the config has been compiled
         */
        public function validate_post(array $config) {

        }
    }