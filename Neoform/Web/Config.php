<?php

    namespace Neoform\Web;

    use Neoform;

    class Config extends Neoform\Config\Model {

        /**
         * User Agent String
         *
         * @return string
         */
        public function getUserAgent() {
            return $this->values['user_agent'];
        }
    }