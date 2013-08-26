<?php

    /**
     * User Hashmethod collection
     */
    class user_hashmethod_collection extends entity_record_collection implements user_hashmethod_definition {

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
            return $this->_preload_one_to_many(
                'user',
                'by_password_hashmethod',
                'user_collection',
                $order_by,
                $offset,
                $limit
            );
        }
    }
