<?php

    namespace neoform\locale;

    use neoform;

    class config extends neoform\config\model {

        /**
         * The array key this config file uses in the compiled configs
         *
         * @return string
         */
        public function get_array_key() {
            return 'locale';
        }

        /**
         * Config default values
         *
         * @return array
         */
        protected function defaults() {
            return [
                // locale translations active
                'active' =>  false,

                // default locale
                'default' => null,

                // allowed locales
                'allowed' => [],

                // which cache engine should be used to store compiled translation dictionaries
                'cache_engine' => null,

                // which cache pool should translations use to store compiled translation dictionaries
                'cache_engine_read'  => null,
                'cache_engine_write' => null,
            ];
        }

        /**
         * Validate the config values
         *
         * @throws neoform\config\exception
         */
        public function validate() {

            if ($this->config['active']) {

                if (empty($this->config['default'])) {
                    throw new neoform\config\exception('"default" must be set');
                }

                if (empty($this->config['allowed'])) {
                    throw new neoform\config\exception('"allowed" must contain at least one locale');
                }

                if (empty($this->config['cache_engine'])) {
                    throw new neoform\config\exception('"cache_engine" must be set');
                }

                if (empty($this->config['cache_engine_write'])) {
                    throw new neoform\config\exception('"cache_engine_write" must be set');
                }

                if (empty($this->config['cache_engine_read'])) {
                    throw new neoform\config\exception('"cache_engine_read" must be set');
                }
            }
        }

        /**
         * Validate the config values after the config has been compiled
         */
        public function validate_post(array $config) {

        }
    }