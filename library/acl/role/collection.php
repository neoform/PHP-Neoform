<?php

    /**
     * Acl Role collection
     */
    class acl_role_collection extends record_collection implements acl_role_definition {

        /**
         * Preload the Acl Group models in this collection
         *
         * @return acl_group_collection
         */
        public function acl_group_collection() {
            return $this->_preload_many_to_many('acl_group_role', 'by_acl_role', 'acl_group');
        }

        /**
         * Preload the Acl Resource models in this collection
         *
         * @return acl_resource_collection
         */
        public function acl_resource_collection() {
            return $this->_preload_many_to_many('acl_role_resource', 'by_acl_role', 'acl_resource');
        }

        /**
         * Preload the User models in this collection
         *
         * @return user_collection
         */
        public function user_collection() {
            return $this->_preload_many_to_many('user_acl_role', 'by_acl_role', 'user');
        }
    }
