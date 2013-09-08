<?php

    namespace neoform;

    /**
     * Acl Resource collection
     */
    class acl_resource_collection extends entity_record_collection implements acl_resource_definition {

        /**
         * Preload the child Acl Resource models in this collection
         *
         * @param array|null   $order_by array of field names (as the key) and sort direction (entity_record_dao::SORT_ASC, entity_record_dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return acl_resource_collection
         */
        public function child_acl_resource_collection(array $order_by=null, $offset=null, $limit=null) {
            return $this->_preload_one_to_many(
                'child_acl_resource_collection',
                'acl_resource',
                'by_parent',
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Preload the child Acl Resource counts
         *
         * @return array counts
         */
        public function child_acl_resource_count() {
            return $this->_preload_counts(
                'child_acl_resource_count',
                'acl_resource',
                'parent_id'
            );
        }

        /**
         * Preload the Acl Role models in this collection
         *
         * @param array        $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset   get PKs starting at this offset
         * @param integer|null $limit    max number of PKs to return
         *
         * @return acl_role_collection
         */
        public function acl_role_collection(array $order_by=null, $offset=null, $limit=null) {
            return $this->_preload_many_to_many(
                'acl_role_collection',
                'acl_role_resource',
                'by_acl_resource',
                'acl_role',
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Preload the Acl Role counts
         *
         * @return array counts
         */
        public function acl_role_count() {
            return $this->_preload_counts(
                'acl_role_count',
                'acl_role_resource',
                'acl_resource_id'
            );
        }

        /**
         * Preload the Acl Resource models in this collection
         *
         * @return acl_resource_collection
         */
        public function parent_acl_resource_collection() {
            return $this->_preload_one_to_one(
                'parent_acl_resource',
                'acl_resource',
                'parent_id'
            );
        }
    }
