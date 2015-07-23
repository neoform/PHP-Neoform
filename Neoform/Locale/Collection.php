<?php

    namespace Neoform\Locale;

    /**
     * Locale collection
     */
    class Collection extends \Neoform\Entity\Record\Collection implements Definition {

        /**
         * Preload the Locale Key models in this collection
         *
         * @param array|null   $order_by array of field names (as the key) and sort direction (Entity\Record_dao::SORT_ASC, Entity\Record_dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return \Neoform\Locale\Key\Collection
         */
        public function locale_key_collection(array $order_by=null, $offset=null, $limit=null) {
            return $this->_preload_one_to_many(
                'locale_key_collection',
                'Neoform\Locale\Key',
                'by_locale',
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
                'Neoform\Locale\Key',
                'locale'
            );
        }

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
                'by_locale',
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
                'locale'
            );
        }

        /**
         * Preload the Locale Key models in this collection
         *
         * @param array        $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset   get PKs starting at this offset
         * @param integer|null $limit    max number of PKs to return
         *
         * @return \Neoform\Locale\Key\Collection
         */
        public function locale_key_collection1(array $order_by=null, $offset=null, $limit=null) {
            return $this->_preload_many_to_many(
                'locale_key_collection1',
                'Neoform\Locale\Key\Message',
                'by_locale',
                'Neoform\Locale\Key',
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
        public function locale_key_count1() {
            return $this->_preload_counts(
                'locale_key_count1',
                'Neoform\Locale\Key\Message',
                'locale'
            );
        }
    }
