<?php

    /**
     * Locale collection
     */
    class locale_collection extends record_collection implements locale_definition {

        /**
         * Preload the Locale Key models in this collection
         *
         * @return locale_key_collection
         */
        public function locale_key_collection() {
            return $this->_preload_one_to_many('locale_key', 'by_locale');
        }

        /**
         * Preload the Locale Key Message models in this collection
         *
         * @return locale_key_message_collection
         */
        public function locale_key_message_collection() {
            return $this->_preload_one_to_many('locale_key_message', 'by_locale');
        }

        /**
         * Preload the Locale Key models in this collection
         *
         * @return locale_key_collection
         */
        public function locale_key_collection2() {
            return $this->_preload_many_to_many('locale_key_message', 'by_locale', 'locale_key');
        }
    }
