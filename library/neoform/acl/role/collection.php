<?php

    namespace neoform\acl\role;

    /**
     * Acl Role collection
     */
    class collection extends \neoform\entity\record\collection implements definition {

        /**
         * Preload the Acl Group models in this collection
         *
         * @param array        $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset   get PKs starting at this offset
         * @param integer|null $limit    max number of PKs to return
         *
         * @return \neoform\acl\group\collection
         */
        public function acl_group_collection(array $order_by=null, $offset=null, $limit=null) {
            return $this->_preload_many_to_many(
                'acl_group_collection',
                'acl\group\role',
                'by_acl_role',
                'acl\group',
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
                'acl\group\role',
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
         * @return \neoform\acl\resource\collection
         */
        public function acl_resource_collection(array $order_by=null, $offset=null, $limit=null) {
            return $this->_preload_many_to_many(
                'acl_resource_collection',
                'acl\role\resource',
                'by_acl_role',
                'acl\resource',
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
                'acl\role\resource',
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
         * @return \neoform\user\collection
         */
        public function user_collection(array $order_by=null, $offset=null, $limit=null) {
            return $this->_preload_many_to_many(
                'user_collection',
                'user\acl\role',
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
                'user\acl\role',
                'acl_role_id'
            );
        }
    }
