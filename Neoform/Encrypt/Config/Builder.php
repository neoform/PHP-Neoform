<?php

    namespace Neoform\Encrypt\Config;

    use Neoform;

    class Builder extends Neoform\Config\Builder {

        /**
         * @return Neoform\Encrypt\Config
         */
        public function build() {
            return new Neoform\Encrypt\Config($this->configValues);
        }

        /**
         * Config default values
         *
         * @return array
         */
        protected function defaults() {
            return [
                'mode'   => MCRYPT_MODE_CBC,
                'cipher' => MCRYPT_RIJNDAEL_256,
            ];
        }

        /**
         * Validate the config values
         *
         * @throws Neoform\Config\Exception
         */
        public function validate() {

            if (empty($this->configValues['mode'])) {
                throw new Neoform\Config\Exception('encrypt requires a valid "mode"');
            }

            if (! in_array($this->configValues['mode'], mcrypt_list_modes())) {
                throw new Neoform\Config\Exception('encrypt requires a valid "mode"');
            }

            if (empty($this->configValues['cipher'])) {
                throw new Neoform\Config\Exception('encrypt requires a valid "cipher"');
            }

            if (! in_array($this->configValues['cipher'], mcrypt_list_algorithms())) {
                throw new Neoform\Config\Exception('encrypt requires a valid "cipher"');
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