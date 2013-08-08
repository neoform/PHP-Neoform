<?php

    class apc_config extends entity_config_defaults {

        protected function defaults() {
            return [
                'key_prefix' => null,
            ];
        }

        /**
         * Validate the config values
         *
         * @throws config_exception
         */
        public function validate() {

        }
    }