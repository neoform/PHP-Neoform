<?php

    namespace neoform\region;

    /**
     * Region collection
     */
    class collection extends \neoform\entity\record\collection implements definition {

        /**
         * Preload the City models in this collection
         *
         * @param array|null   $order_by array of field names (as the key) and sort direction (entity\record_dao::SORT_ASC, entity\record_dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return \neoform\city\collection
         */
        public function city_collection(array $order_by=null, $offset=null, $limit=null) {
            return $this->_preload_one_to_many(
                'city_collection',
                '\neoform\city',
                'by_region',
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Preload the City counts
         *
         * @return array counts
         */
        public function city_count() {
            return $this->_preload_counts(
                'city_count',
                '\neoform\city',
                'region_id'
            );
        }

        /**
         * Preload the Country models in this collection
         *
         * @return \neoform\country\collection
         */
        public function country_collection() {
            return $this->_preload_one_to_one(
                'country',
                '\neoform\country',
                'country_id'
            );
        }
    }
