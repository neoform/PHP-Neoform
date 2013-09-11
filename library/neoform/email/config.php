<?php

    namespace neoform\email;

    use neoform;

    class config extends neoform\config\defaults {

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
    }