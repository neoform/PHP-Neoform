<?php

    /**
     * Acl Resource collection
     */
    class acl_resource_collection extends record_collection implements acl_resource_definition {

        /**
         * Preload the child Acl Resource models in this collection
         *
         * @return acl_resource_collection
         */
        public function child_acl_resource_collection() {
            return $this->_preload_one_to_many('acl_resource', 'by_parent', 'child_acl_resource_collection');
        }

        /**
         * Preload the Acl Role models in this collection
         *
         * @return acl_role_collection
         */
        public function acl_role_collection() {
            return $this->_preload_many_to_many('acl_role_resource', 'by_acl_resource', 'acl_role');
        }

        /**
         * Preload the Acl Resource models in this collection
         *
         * @return acl_resource_collection
         */
        public function parent_acl_resource_collection() {
            return $this->_preload_one_to_one('acl_resource', 'parent_id', 'parent_acl_resource');
        }
    }
