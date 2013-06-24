<?php

    /**
     * Locale Key Message collection
     */
    class locale_key_message_collection extends record_collection implements locale_key_message_definition {

        /**
         * Preload the Locale Key models in this collection
         *
         * @return locale_key_collection
         */
        public function locale_key_collection() {
            return $this->_preload_one_to_one('locale_key', 'key_id');
        }

        /**
         * Preload the Locale models in this collection
         *
         * @return locale_collection
         */
        public function locale_collection() {
            return $this->_preload_one_to_one('locale', 'locale');
        }
    }
