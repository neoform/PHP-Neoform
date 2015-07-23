<?php

    namespace Neoform\Locale\Config;

    use Neoform;

    class Builder extends Neoform\Config\Builder {

        /**
         * @return Neoform\Locale\Config
         */
        public function build() {
            return new Neoform\Locale\Config($this->configValues);
        }

        /**
         * Config default values
         *
         * @return array
         */
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

        /**
         * Validate the config values
         *
         * @throws Neoform\Config\Exception
         */
        public function validate() {

            if ($this->configValues['active']) {

                if (empty($this->configValues['default'])) {
                    throw new Neoform\Config\Exception('"default" must be set');
                }

                if (empty($this->configValues['allowed'])) {
                    throw new Neoform\Config\Exception('"allowed" must contain at least one locale');
                }

                if (empty($this->configValues['cache_engine'])) {
                    throw new Neoform\Config\Exception('"cache_engine" must be set');
                }

                if (empty($this->configValues['cache_engine_write'])) {
                    throw new Neoform\Config\Exception('"cache_engine_write" must be set');
                }

                if (empty($this->configValues['cache_engine_read'])) {
                    throw new Neoform\Config\Exception('"cache_engine_read" must be set');
                }
            }
        }

        /**
         * Validate the config values after the config has been compiled
         */
        public function validatePost(Neoform\Config\Collection $configs) {

        }
    }