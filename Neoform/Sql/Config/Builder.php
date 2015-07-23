<?php

    namespace Neoform\Sql\Config;

    use Neoform;

    class Builder extends Neoform\Config\Builder {

        /**
         * @return Neoform\Sql\Config
         */
        public function build() {
            return new Neoform\Sql\Config($this->configValues);
        }

        /**
         * Config default values
         *
         * @return array
         */
        protected function defaults() {
            return [
                // SQL charset (encoding)
                'encoding' => 'utf8',

                // the connection name that is use when all else fails to [required]
                'default_pool_read'  => null,
                'default_pool_write' => null,

                // Server pools
                'pools' => [],
            ];
        }

        /**
         * Validate the config values
         *
         * @throws Neoform\Config\Exception
         */
        public function validate() {

            if (empty($this->configValues['default_pool_read'])) {
                throw new Neoform\Config\Exception('"default_pool_read" must be set');
            }

            if (empty($this->configValues['default_pool_write'])) {
                throw new Neoform\Config\Exception('"default_pool_write" must be set');
            }

            if (empty($this->configValues['pools']) || ! is_array($this->configValues['pools']) || ! count($this->configValues['pools'])) {
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