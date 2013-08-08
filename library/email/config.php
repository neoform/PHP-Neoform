<?php

    class email_config extends entity_config_defaults {

        protected function defaults() {
            return [
                // random string used to give users a hash (based on their user info) that they can disable their
                // email notifications
                'unsubscribe_secret' => null,
            ];
        }

        /**
         * Validate the config values
         *
         * @throws config_exception
         */
        public function validate() {
            if (empty($this->config['unsubscribe_secret'])) {
                throw new config_exception('"unsubscribe_secret" must be set');
            }
        }
    }