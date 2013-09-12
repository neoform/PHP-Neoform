<?php

    namespace neoform\locale;

    /**
     * Locale collection
     */
    class collection extends \neoform\entity\record\collection implements definition {

        /**
         * Preload the Locale Key models in this collection
         *
         * @param array|null   $order_by array of field names (as the key) and sort direction (entity\record_dao::SORT_ASC, entity\record_dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return \neoform\locale\key\collection
         */
        public function locale_key_collection(array $order_by=null, $offset=null, $limit=null) {
            return $this->_preload_one_to_many(
                'locale_key_collection',
                '\neoform\locale\key',
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
                '\neoform\locale\key',
                'locale'
            );
        }

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
                '\neoform\locale\key\message',
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
                '\neoform\locale\key\message',
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
         * @return \neoform\locale\key\collection
         */
        public function locale_key_collection1(array $order_by=null, $offset=null, $limit=null) {
            return $this->_preload_many_to_many(
                'locale_key_collection1',
                '\neoform\locale\key\message',
                'by_locale',
                '\neoform\locale\key',
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
                '\neoform\locale\key\message',
                'locale'
            );
        }
    }
