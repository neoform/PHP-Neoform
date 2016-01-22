<?php

    namespace Neoform\Acl\Resource;

    /**
     * Acl Resource collection
     */
    class Collection extends \Neoform\Entity\Record\Collection {

        // Load entity details into the class
        use Details;

        /**
         * Preload the child Acl Resource models in this collection
         *
         * @param array|null   $order_by array of field names (as the key) and sort direction (Entity\Record_dao::SORT_ASC, Entity\Record_dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return \Neoform\Acl\Resource\Collection
         */
        public function child_acl_resource_collection(array $order_by=null, $offset=null, $limit=null) {
            return $this->_preload_one_to_many(
                'child_acl_resource_collection',
                'Neoform\Acl\Resource',
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
                'Neoform\Acl\Resource',
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
         * @return \Neoform\Acl\Role\Collection
         */
        public function acl_role_collection(array $order_by=null, $offset=null, $limit=null) {
            return $this->_preload_many_to_many(
                'acl_role_collection',
                'Neoform\Acl\Role\Resource',
                'by_acl_resource',
                'Neoform\Acl\Role',
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
                'Neoform\Acl\Role\Resource',
                'acl_resource_id'
            );
        }

        /**
         * Preload the Acl Resource models in this collection
         *
         * @return \Neoform\Acl\Resource\Collection
         */
        public function parent_acl_resource_collection() {
            return $this->_preload_one_to_one(
                'parent_acl_resource',
                'Neoform\Acl\Resource',
                'parent_id'
            );
        }
    }
