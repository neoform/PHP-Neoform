<?php

    /**
     * Acl Role collection
     */
    class acl_role_collection extends entity_record_collection implements acl_role_definition {

        /**
         * Preload the Acl Group models in this collection
         *
         * @param array        $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset   get PKs starting at this offset
         * @param integer|null $limit    max number of PKs to return
         *
         * @return acl_group_collection
         */
        public function acl_group_collection(array $order_by=null, $offset=null, $limit=null) {
            return $this->_preload_many_to_many(
                'acl_group_role',
                'by_acl_role',
                'acl_group',
                'acl_group_collection',
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Preload the Acl Resource models in this collection
         *
         * @param array        $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset   get PKs starting at this offset
         * @param integer|null $limit    max number of PKs to return
         *
         * @return acl_resource_collection
         */
        public function acl_resource_collection(array $order_by=null, $offset=null, $limit=null) {
            return $this->_preload_many_to_many(
                'acl_role_resource',
                'by_acl_role',
                'acl_resource',
                'acl_resource_collection',
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Preload the User models in this collection
         *
         * @param array        $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset   get PKs starting at this offset
         * @param integer|null $limit    max number of PKs to return
         *
         * @return user_collection
         */
        public function user_collection(array $order_by=null, $offset=null, $limit=null) {
            return $this->_preload_many_to_many(
                'user_acl_role',
                'by_acl_role',
                'user',
                'user_collection',
                $order_by,
                $offset,
                $limit
            );
        }
    }
