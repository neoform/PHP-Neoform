<?php

    namespace neoform\encrypt;

    use neoform;

    class config extends neoform\config\model {

        /**
         * The array key this config file uses in the compiled configs
         *
         * @return string
         */
        public function get_array_key() {
            return 'encrypt';
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
         * @throws neoform\config\exception
         */
        public function validate() {

            if (empty($this->config['mode'])) {
                throw new neoform\config\exception('encrypt requires a valid "mode"');
            }

            if (! in_array($this->config['mode'], mcrypt_list_modes())) {
                throw new neoform\config\exception('encrypt requires a valid "mode"');
            }

            if (empty($this->config['cipher'])) {
                throw new neoform\config\exception('encrypt requires a valid "cipher"');
            }

            if (! in_array($this->config['cipher'], mcrypt_list_algorithms())) {
                throw new neoform\config\exception('encrypt requires a valid "cipher"');
            }
        }

        /**
         * Validate the config values after the config has been compiled
         */
        public function validate_post(array $config) {

        }
    }