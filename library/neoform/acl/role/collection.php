<?php

    namespace neoform;

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
                'acl_group_collection',
                'acl_group_role',
                'by_acl_role',
                'acl_group',
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Preload the Acl Group counts
         *
         * @return array counts
         */
        public function acl_group_count() {
            return $this->_preload_counts(
                'acl_group_count',
                'acl_group_role',
                'acl_role_id'
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
                'acl_resource_collection',
                'acl_role_resource',
                'by_acl_role',
                'acl_resource',
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Preload the Acl Resource counts
         *
         * @return array counts
         */
        public function acl_resource_count() {
            return $this->_preload_counts(
                'acl_resource_count',
                'acl_role_resource',
                'acl_role_id'
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
                'user_collection',
                'user_acl_role',
                'by_acl_role',
                'user',
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Preload the User counts
         *
         * @return array counts
         */
        public function user_count() {
            return $this->_preload_counts(
                'user_count',
                'user_acl_role',
                'acl_role_id'
            );
        }
    }
