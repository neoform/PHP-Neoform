<?php

    namespace neoform\locale\key;

    /**
     * Locale Key collection
     */
    class collection extends \neoform\entity\record\collection implements definition {

        /**
         * Preload the Locale Key Message models in this collection
         *
         * @param array|null   $order_by array of field names (as the key) and sort direction (entity\record_dao::SORT_ASC, entity\record_dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return \neoform\locale\key\message\collection
         */
        public function locale_key_message_collection(array $order_by=null, $offset=null, $limit=null) {
            return $this->_preload_one_to_many(
                'locale_key_message_collection',
                'neoform\locale\key\message',
                'by_key',
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Preload the Locale Key Message counts
         *
         * @return array counts
         */
        public function locale_key_message_count() {
            return $this->_preload_counts(
                'locale_key_message_count',
                'neoform\locale\key\message',
                'key_id'
            );
        }

        /**
         * Preload the Locale models in this collection
         *
         * @param array        $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset   get PKs starting at this offset
         * @param integer|null $limit    max number of PKs to return
         *
         * @return \neoform\locale\collection
         */
        public function locale_collection(array $order_by=null, $offset=null, $limit=null) {
            return $this->_preload_many_to_many(
                'locale_collection',
                'neoform\locale\key\message',
                'by_key',
                'neoform\locale',
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Preload the Locale counts
         *
         * @return array counts
         */
        public function locale_count() {
            return $this->_preload_counts(
                'locale_count',
                'neoform\locale\key\message',
                'key_id'
            );
        }

        /**
         * Preload the Locale models in this collection
         *
         * @return \neoform\locale\collection
         */
        public function locale_collection1() {
            return $this->_preload_one_to_one(
                'locale',
                'neoform\locale',
                'locale'
            );
        }

        /**
         * Preload the Locale Namespace models in this collection
         *
         * @return \neoform\locale\nspace\collection
         */
        public function locale_namespace_collection() {
            return $this->_preload_one_to_one(
                'locale_namespace',
                'neoform\locale\nspace',
                'namespace_id'
            );
        }
    }
