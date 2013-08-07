<?php

    /**
     * Acl Group collection
     */
    class acl_group_collection extends entity_record_collection implements acl_group_definition {

        /**
         * Preload the Acl Role models in this collection
         *
         * @return acl_role_collection
         */
        public function acl_role_collection() {
            return $this->_preload_many_to_many('acl_group_role', 'by_acl_group', 'acl_role');
        }

        /**
         * Preload the User models in this collection
         *
         * @return user_collection
         */
        public function user_collection() {
            return $this->_preload_many_to_many('acl_group_user', 'by_acl_group', 'user');
        }
    }
