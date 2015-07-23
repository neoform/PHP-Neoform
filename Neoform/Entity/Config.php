<?php

    namespace Neoform\Entity;

    use Neoform;

    class Config extends Neoform\Config\Model {

        /**
         * When no entity source engine is defined in definition file, use this engine
         *
         * @return string[]
         */
        public function getDefaults() {
            return $this->values['defaults'];
        }

        /**
         * @return string[]
         */
        public function getOverrides() {
            return $this->values['overrides'];
        }
    }