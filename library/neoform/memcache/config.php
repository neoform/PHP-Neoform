<?php

    namespace neoform\memcache;

    use neoform\config\defaults;
    use neoform\config\exception;

    class config extends defaults {

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
         * @throws exception
         */
        public function validate() {

            if (empty($this->config['default_pool'])) {
                throw new exception('"default_pool" must be set');
            }

            if (empty($this->config['pools']) || ! is_array($this->config['pools']) || ! $this->config['pools']) {
                throw new exception('"pools" must contain at least one server');
            }
        }
    }