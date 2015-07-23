<?php

    namespace Neoform\Encrypt;

    use Neoform;

    class Config extends Neoform\Config\Model {

        /**
         * @return string
         */
        public function getMode() {
            return $this->values['mode'];
        }

        /**
         * @return string
         */
        public function getCipher() {
            return $this->values['cipher'];
        }
    }