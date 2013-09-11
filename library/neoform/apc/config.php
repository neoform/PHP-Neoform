<?php

    namespace neoform\apc;

    use neoform;

    class config extends neoform\config\defaults {

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
    }