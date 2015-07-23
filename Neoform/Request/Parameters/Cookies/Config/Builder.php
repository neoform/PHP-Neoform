<?php

    namespace Neoform\Request\Parameters\Cookies\Config;

    use Neoform;

    class Builder extends Neoform\Config\Builder {

        /**
         * @return Neoform\Locale\Config
         */
        public function build() {
            return new Neoform\Request\Parameters\Cookies\Config($this->configValues);
        }

        /**
         * Config default values
         *
         * @return array
         */
        protected function defaults() {
            return [
                // 4 years in seconds [required]
                'ttl' => 126227704,

                // cookie path [optional]
                'path' => '/',

                // only allow cookies to be read via https [required]
                'secure' => false,

                // only allow cookies to be read by http and no javascript
                'httponly' => true,
            ];
        }

        /**
         * Validate the config values
         *
         * @throws Neoform\Config\Exception
         */
        public function validate() {

            if (! is_int($this->configValues['ttl'])) {
                throw new Neoform\Config\Exception('\'ttl\' must be an integer');
            }

            if (empty($this->configValues['path'])) {
                throw new Neoform\Config\Exception('\'path\' must be set');
            }

            if (! is_bool($this->configValues['secure'])) {
                throw new Neoform\Config\Exception('\'secure\' must be a bool');
            }

            if (! is_bool($this->configValues['httponly'])) {
                throw new Neoform\Config\Exception('\'httponly\' must be a bool');
            }
        }

        /**
         * Validate the config values after the config has been compiled
         */
        public function validatePost(Neoform\Config\Collection $configs) {

        }
    }