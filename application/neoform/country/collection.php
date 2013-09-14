<?php

    namespace neoform\country;

    /**
     * Country collection
     */
    class collection extends \neoform\entity\record\collection implements definition {

        /**
         * Preload the Region models in this collection
         *
         * @param array|null   $order_by array of field names (as the key) and sort direction (entity\record_dao::SORT_ASC, entity\record_dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return \neoform\region\collection
         */
        public function region_collection(array $order_by=null, $offset=null, $limit=null) {
            return $this->_preload_one_to_many(
                'region_collection',
                'region',
                'by_country',
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Preload the Region counts
         *
         * @return array counts
         */
        public function region_count() {
            return $this->_preload_counts(
                'region_count',
                'region',
                'country_id'
            );
        }
    }
