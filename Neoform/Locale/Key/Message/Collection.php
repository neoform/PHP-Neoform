<?php

    namespace Neoform\Locale\Key\Message;

    /**
     * Locale Key Message collection
     */
    class Collection extends \Neoform\Entity\Record\Collection {

        // Load entity details into the class
        use Details;

        /**
         * Preload the Locale Key models in this collection
         *
         * @return \Neoform\Locale\Key\Collection
         */
        public function locale_key_collection() {
            return $this->_preload_one_to_one(
                'locale_key',
                'Neoform\Locale\Key',
                'key_id'
            );
        }

        /**
         * Preload the Locale models in this collection
         *
         * @return \Neoform\Locale\Collection
         */
        public function locale_collection() {
            return $this->_preload_one_to_one(
                'locale',
                'Neoform\Locale',
                'locale'
            );
        }
    }
