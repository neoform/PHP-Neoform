<?php

    namespace Neoform\Locale\Key;

    /**
     * Locale Key collection
     */
    class Collection extends \Neoform\Entity\Record\Collection implements Definition {

        /**
         * Preload the Locale Key Message models in this collection
         *
         * @param array|null   $order_by array of field names (as the key) and sort direction (Entity\Record_dao::SORT_ASC, Entity\Record_dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return \Neoform\Locale\Key\Message\Collection
         */
        public function locale_key_message_collection(array $order_by=null, $offset=null, $limit=null) {
            return $this->_preload_one_to_many(
                'locale_key_message_collection',
                'Neoform\Locale\Key\Message',
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
                'Neoform\Locale\Key\Message',
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
         * @return \Neoform\Locale\Collection
         */
        public function locale_collection(array $order_by=null, $offset=null, $limit=null) {
            return $this->_preload_many_to_many(
                'locale_collection',
                'Neoform\Locale\Key\Message',
                'by_key',
                'Neoform\Locale',
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
                'Neoform\Locale\Key\Message',
                'key_id'
            );
        }

        /**
         * Preload the Locale models in this collection
         *
         * @return \Neoform\Locale\Collection
         */
        public function locale_collection1() {
            return $this->_preload_one_to_one(
                'locale',
                'Neoform\Locale',
                'locale'
            );
        }

        /**
         * Preload the Locale Namespace models in this collection
         *
         * @return \Neoform\Locale\Nspace\Collection
         */
        public function locale_namespace_collection() {
            return $this->_preload_one_to_one(
                'locale_namespace',
                'Neoform\Locale\Nspace',
                'namespace_id'
            );
        }
    }
