<?php

    /**
     * Site collection
     */
    class site_collection extends entity_record_collection implements site_definition {

        /**
         * Preload the User models in this collection
         *
         * @param array|null   $order_by array of field names (as the key) and sort direction (entity_record_dao::SORT_ASC, entity_record_dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return user_collection
         */
        public function user_collection(array $order_by=null, $offset=null, $limit=null) {
            return $this->_preload_many_to_many(
                'user_site',
                'by_site',
                'user',
                'user_collection',
                $order_by,
                $offset,
                $limit
            );
        }
    }
