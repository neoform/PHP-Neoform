<?php

    namespace neoform\email;

    use neoform;

    class config extends neoform\config\model {

        /**
         * The array key this config file uses in the compiled configs
         *
         * @return string
         */
        public function get_array_key() {
            return 'email';
        }

        /**
         * Config default values
         *
         * @return array
         */
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
         * @throws neoform\config\exception
         */
        public function validate() {
            if (empty($this->config['unsubscribe_secret'])) {
                throw new neoform\config\exception('"unsubscribe_secret" must be set');
            }
        }

        /**
         * Validate the config values after the config has been compiled
         */
        public function validate_post(array $config) {

        }
    }