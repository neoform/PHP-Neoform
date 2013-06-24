<?php

    /**
     * Locale Key collection
     */
    class locale_key_collection extends record_collection implements locale_key_definition {

        /**
         * Preload the Locale Key Message models in this collection
         *
         * @return locale_key_message_collection
         */
        public function locale_key_message_collection() {
            return $this->_preload_one_to_many('locale_key_message', 'by_key');
        }

        /**
         * Preload the Locale models in this collection
         *
         * @return locale_collection
         */
        public function locale_collection_by_key() {
            return $this->_preload_many_to_many('locale_key_message', 'by_key', 'locale');
        }

        /**
         * Preload the Locale models in this collection
         *
         * @return locale_collection
         */
        public function locale_collection() {
            return $this->_preload_one_to_one('locale', 'locale');
        }

        /**
         * Preload the Locale Namespace models in this collection
         *
         * @return locale_namespace_collection
         */
        public function locale_namespace_collection() {
            return $this->_preload_one_to_one('locale_namespace', 'namespace_id');
        }
    }
