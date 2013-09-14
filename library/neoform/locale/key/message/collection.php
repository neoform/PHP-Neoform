<?php

    namespace neoform\locale\key\message;

    /**
     * Locale Key Message collection
     */
    class collection extends \neoform\entity\record\collection implements definition {

        /**
         * Preload the Locale Key models in this collection
         *
         * @return \neoform\locale\key\collection
         */
        public function locale_key_collection() {
            return $this->_preload_one_to_one(
                'locale_key',
                'locale\key',
                'key_id'
            );
        }

        /**
         * Preload the Locale models in this collection
         *
         * @return \neoform\locale\collection
         */
        public function locale_collection() {
            return $this->_preload_one_to_one(
                'locale',
                'locale',
                'locale'
            );
        }
    }
