<?php

    /**
     * Locale Namespace collection
     */
    class locale_namespace_collection extends entity_record_collection implements locale_namespace_definition {

        /**
         * Preload the Locale Key models in this collection
         *
         * @return locale_key_collection
         */
        public function locale_key_collection() {
            return $this->_preload_one_to_many('locale_key', 'by_namespace');
        }
    }
