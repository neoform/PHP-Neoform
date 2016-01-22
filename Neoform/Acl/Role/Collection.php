<?php

    namespace Neoform\Acl\Role;

    /**
     * Acl Role collection
     */
    class Collection extends \Neoform\Entity\Record\Collection {

        // Load entity details into the class
        use Details;

        /**
         * Preload the Acl Group models in this collection
         *
         * @param array        $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset   get PKs starting at this offset
         * @param integer|null $limit    max number of PKs to return
         *
         * @return \Neoform\Acl\Group\Collection
         */
        public function acl_group_collection(array $order_by=null, $offset=null, $limit=null) {
            return $this->_preload_many_to_many(
                'acl_group_collection',
                'Neoform\Acl\Group\Role',
                'by_acl_role',
                'Neoform\Acl\Group',
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
                'Neoform\Acl\Group\Role',
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
         * @return \Neoform\Acl\Resource\Collection
         */
        public function acl_resource_collection(array $order_by=null, $offset=null, $limit=null) {
            return $this->_preload_many_to_many(
                'acl_resource_collection',
                'Neoform\Acl\Role\Resource',
                'by_acl_role',
                'Neoform\Acl\Resource',
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
                'Neoform\Acl\Role\Resource',
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
         * @return \Neoform\User\Collection
         */
        public function user_collection(array $order_by=null, $offset=null, $limit=null) {
            return $this->_preload_many_to_many(
                'user_collection',
                'Neoform\User\Acl\Role',
                'by_acl_role',
                'Neoform\User',
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
                'Neoform\User\Acl\Role',
                'acl_role_id'
            );
        }
    }
