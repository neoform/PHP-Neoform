<?php

    namespace neoform\core;

    use neoform;

    class config extends neoform\config\model {

        /**
         * The array key this config file uses in the compiled configs
         *
         * @return string
         */
        public function get_array_key() {
            return 'core';
        }

        /**
         * Config default values
         *
         * @return array
         */
        protected function defaults() {
            return [
                'site_name' => null,

                // When using multiple sites with a common db, user accounts are created under this site_id
                'site_id' => 1,

                // manually set the timezone [required]
                'timezone' => 'UTC',

                // page output encoding [required]
                'encoding' => 'utf-8',
            ];
        }

        /**
         * Validate the config values
         */
        public function validate() {
            if ((int) $this->config['site_id'] != $this->config['site_id'] || $this->config['site_id'] < 1) {
                throw new \exception('["site_id"] must be an unsigned integer');
            }
        }

        /**
         * Validate the config values after the config has been compiled
         */
        public function validate_post(array $config) {

        }
    }