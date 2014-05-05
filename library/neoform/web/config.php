<?php

    namespace neoform\web;

    use neoform;

    class config extends neoform\config\defaults {

        protected function defaults() {
            return [
                // User agent
                'user_agent' => null,
            ];
        }

        /**
         * Validate the config values
         *
         * @throws neoform\config\exception
         */
        public function validate() {

            if (empty($this->config['user_agent'])) {
                throw new neoform\config\exception('"user_agent" must be set');
            }
        }
    }