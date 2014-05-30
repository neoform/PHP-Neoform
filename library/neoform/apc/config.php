<?php

    namespace neoform\apc;

    use neoform;

    class config extends neoform\config\model {

        /**
         * The array key this config file uses in the compiled configs
         *
         * @return string
         */
        public function get_array_key() {
            return 'apc';
        }

        /**
         * Config default values
         *
         * @return array
         */
        protected function defaults() {
            return [
                'key_prefix' => null,
            ];
        }

        /**
         * Validate the config values
         *
         * @throws neoform\config\exception
         */
        public function validate() {

        }

        /**
         * Validate the config values after the config has been compiled
         */
        public function validate_post(array $config) {

        }
    }