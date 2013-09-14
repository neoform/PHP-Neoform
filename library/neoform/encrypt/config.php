<?php

    namespace neoform\encrypt;

    use neoform;

    class config extends neoform\config\defaults {

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
    }