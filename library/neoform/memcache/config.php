<?php

    namespace neoform\memcache;

    use neoform;

    class config extends neoform\config\defaults {

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
         * @throws neoform\config\exception
         */
        public function validate() {

            if (empty($this->config['default_pool'])) {
                throw new neoform\config\exception('"default_pool" must be set');
            }

            if (empty($this->config['pools']) || ! is_array($this->config['pools']) || ! $this->config['pools']) {
                throw new neoform\config\exception('"pools" must contain at least one server');
            }
        }
    }