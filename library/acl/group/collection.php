<?php

    /**
     * Acl Group collection
     */
    class acl_group_collection extends entity_record_collection implements acl_group_definition {

        /**
         * Preload the Acl Role models in this collection
         *
         * @param array|null   $order_by array of field names (as the key) and sort direction (entity_record_dao::SORT_ASC, entity_record_dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return acl_role_collection
         */
        public function acl_role_collection(array $order_by=null, $offset=null, $limit=null) {
            return $this->_preload_many_to_many(
                'acl_group_role',
                'by_acl_group',
                'acl_role',
                'acl_role_collection',
                $order_by,
                $offset,
                $limit
            );
        }

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
                'acl_group_user',
                'by_acl_group',
                'user',
                'user_collection',
                $order_by,
                $offset,
                $limit
            );
        }
    }
