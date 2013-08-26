<?php

    /**
     * Locale collection
     */
    class locale_collection extends entity_record_collection implements locale_definition {

        /**
         * Preload the Locale Key models in this collection
         *
         * @param array|null   $order_by array of field names (as the key) and sort direction (entity_record_dao::SORT_ASC, entity_record_dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return locale_key_collection
         */
        public function locale_key_collection(array $order_by=null, $offset=null, $limit=null) {
            return $this->_preload_one_to_many(
                'locale_key',
                'by_locale',
                'locale_key_collection',
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Preload the Locale Key Message models in this collection
         *
         * @param array|null   $order_by array of field names (as the key) and sort direction (entity_record_dao::SORT_ASC, entity_record_dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return locale_key_message_collection
         */
        public function locale_key_message_collection(array $order_by=null, $offset=null, $limit=null) {
            return $this->_preload_one_to_many(
                'locale_key_message',
                'by_locale',
                'locale_key_message_collection',
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Preload the Locale Key models in this collection
         *
         * @return locale_key_collection
         */
        public function locale_key_collection1() {
            return $this->_preload_many_to_many(
                'locale_key_message',
                'by_locale',
                'locale_key',
                'locale_key_collection1'
            );
        }
    }
