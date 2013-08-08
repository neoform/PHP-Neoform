<?php

    class locale_config extends entity_config_defaults {

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

        public function validate() {

            if ($this->config['active']) {

                if (empty($this->config['default'])) {
                    throw new config_exception('"default" must be set');
                }

                if (empty($this->config['allowed'])) {
                    throw new config_exception('"allowed" must contain at least one locale');
                }

                if (empty($this->config['cache_engine'])) {
                    throw new config_exception('"cache_engine" must be set');
                }

                if (empty($this->config['cache_engine_write'])) {
                    throw new config_exception('"cache_engine_write" must be set');
                }

                if (empty($this->config['cache_engine_read'])) {
                    throw new config_exception('"cache_engine_read" must be set');
                }
            }
        }
    }