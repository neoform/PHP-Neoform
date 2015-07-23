<?php

    namespace Neoform\Memcache\Config;

    use Neoform;

    class Builder extends Neoform\Config\Builder {

        /**
         * @return Neoform\Memcache\Config
         */
        public function build() {
            return new Neoform\Memcache\Config($this->configValues);
        }

        /**
         * Config default values
         *
         * @return array
         */
        protected function defaults() {
            return [

                //leave blank (empty string) if no prefix is needed
                //this prefix is useful if you have multiple instances of the same code on the same memcache pool (maybe prod/dev on one memcache pool)
                'key_prefix' => null,

                'default_pool' => null,

                'pools' => [],
            ];
        }

        /**
         * Validate the config values
         *
         * @throws Neoform\Config\Exception
         */
        public function validate() {

            if (empty($this->configValues['default_pool'])) {
                throw new Neoform\Config\Exception('"default_pool" must be set');
            }

            if (empty($this->configValues['pools']) || ! is_array($this->configValues['pools']) || ! $this->configValues['pools']) {
                throw new Neoform\Config\Exception('"pools" must contain at least one server');
            }
        }

        /**
         * Validate the config values after the config has been compiled
         *
         * @param Neoform\Config\Collection $configs
         */
        public function validatePost(Neoform\Config\Collection $configs) {

        }
    }