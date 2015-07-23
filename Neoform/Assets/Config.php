<?php

    namespace Neoform\Assets;

    use Neoform;

    class Config extends Neoform\Config\Model {

        /**
         * @return bool
         */
        public function isEnabled() {
            return (bool) $this->values['enabled'];
        }

        /**
         * @return array
         */
        public function getTypes() {
            return $this->values['types'];
        }
    }