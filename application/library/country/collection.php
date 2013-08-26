<?php

    /**
     * Country collection
     */
    class country_collection extends entity_record_collection implements country_definition {

        /**
         * Preload the Region models in this collection
         *
         * @param array|null   $order_by array of field names (as the key) and sort direction (entity_record_dao::SORT_ASC, entity_record_dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return region_collection
         */
        public function region_collection(array $order_by=null, $offset=null, $limit=null) {
            return $this->_preload_one_to_many(
                'region',
                'by_country',
                'region_collection',
                $order_by,
                $offset,
                $limit
            );
        }
    }
