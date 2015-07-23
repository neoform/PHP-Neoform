<?php

    namespace Neoform\Core;

    use Neoform;

    class Config extends Neoform\Config\Model {

        /**
         * @return string
         */
        public function getSiteName() {
            return $this->values['site_name'];
        }

        /**
         * @return string
         */
        public function getSiteId() {
            return $this->values['site_id'];
        }

        /**
         * @return string
         */
        public function getTimeZone() {
            return $this->values['timezone'];
        }

        /**
         * @return string
         */
        public function getEncoding() {
            return $this->values['encoding'];
        }

        /**
         * @return string
         */
        public function getDefaultErrorController() {
            return $this->values['default_error_controller'];
        }

        /**
         * @return string
         */
        public function getDefaultErrorControllerAction() {
            return $this->values['default_error_controller_action'];
        }
    }