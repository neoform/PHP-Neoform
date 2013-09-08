<?php

    namespace neoform;

    /**
     * Locale Namespace collection
     */
    class locale_namespace_collection extends entity_record_collection implements locale_namespace_definition {

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
                'locale_key_collection',
                'locale_key',
                'by_namespace',
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Preload the Locale Key counts
         *
         * @return array counts
         */
        public function locale_key_count() {
            return $this->_preload_counts(
                'locale_key_count',
                'locale_key',
                'namespace_id'
            );
        }
    }
