<?php

    namespace neoform\core;

    class config extends \neoform\config\defaults {

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
    }