<?php

    namespace Neoform\Assets\Processor\Js;

    use Neoform;

    class Config extends Neoform\Config\Model {

        /**
         * @return string[]
         */
        public function getSearchReplace() {
            return $this->values['search_replace'];
        }

        /**
         * @return string[]
         */
        public function getPatterns() {
            return $this->values['patterns'];
        }
    }