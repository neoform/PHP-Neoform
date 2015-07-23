<?php

    namespace Neoform\Request\Parameters\Cookies;

    use Neoform;

    class Config extends Neoform\Config\Model {

        /**
         * Cookie timeout (in seconds)
         *
         * @return int
         */
        public function getTtl() {
            return (int) $this->values['ttl'];
        }

        /**
         * Cookie Path
         *
         * @return string
         */
        public function getPath() {
            return $this->values['path'];
        }

        /**
         * Only allow cookies to be read via https
         *
         * @return bool
         */
        public function isSecure() {
            return (bool) $this->values['secure'];
        }

        /**
         * Only allow cookies to be read by http and no javascript
         *
         * @return bool
         */
        public function isHttpOnly() {
            return (bool) $this->values['httponly'];
        }
    }