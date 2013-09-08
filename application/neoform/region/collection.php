<?php

    namespace neoform;

    /**
     * Region collection
     */
    class region_collection extends entity_record_collection implements region_definition {

        /**
         * Preload the City models in this collection
         *
         * @param array|null   $order_by array of field names (as the key) and sort direction (entity_record_dao::SORT_ASC, entity_record_dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return city_collection
         */
        public function city_collection(array $order_by=null, $offset=null, $limit=null) {
            return $this->_preload_one_to_many(
                'city_collection',
                'city',
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
                'city',
                'region_id'
            );
        }

        /**
         * Preload the Country models in this collection
         *
         * @return country_collection
         */
        public function country_collection() {
            return $this->_preload_one_to_one(
                'country',
                'country',
                'country_id'
            );
        }
    }
