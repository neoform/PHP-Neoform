<?php

    /**
     * User collection
     */
    class user_collection extends entity_record_collection implements user_definition {

        /**
         * Preload the Acl Group models in this collection
         *
         * @return acl_group_collection
         */
        public function acl_group_collection() {
            return $this->_preload_many_to_many(
                'acl_group_user',
                'by_user',
                'acl_group',
                'acl_group_collection'
            );
        }

        /**
         * Preload the Auth models in this collection
         *
         * @param array|null   $order_by array of field names (as the key) and sort direction (entity_record_dao::SORT_ASC, entity_record_dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return auth_collection
         */
        public function auth_collection(array $order_by=null, $offset=null, $limit=null) {
            return $this->_preload_one_to_many(
                'auth',
                'by_user',
                'auth_collection',
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Preload the Acl Role models in this collection
         *
         * @return acl_role_collection
         */
        public function acl_role_collection() {
            return $this->_preload_many_to_many(
                'user_acl_role',
                'by_user',
                'acl_role',
                'acl_role_collection'
            );
        }

        /**
         * Preload the User Date models in this collection
         *
         * @return user_date_collection
         */
        public function user_date_collection() {
            return $this->_preload_one_to_one(
                'user_date',
                'id',
                'user_date'
            );
        }

        /**
         * Preload the User Lostpassword models in this collection
         *
         * @return user_lostpassword_collection
         */
        public function user_lostpassword_collection() {
            return $this->_preload_one_to_one(
                'user_lostpassword',
                'id',
                'user_lostpassword'
            );
        }

        /**
         * Preload the Site models in this collection
         *
         * @return site_collection
         */
        public function site_collection() {
            return $this->_preload_many_to_many(
                'user_site',
                'by_user',
                'site',
                'site_collection'
            );
        }

        /**
         * Preload the User Hashmethod models in this collection
         *
         * @return user_hashmethod_collection
         */
        public function user_hashmethod_collection() {
            return $this->_preload_one_to_one(
                'user_hashmethod',
                'password_hashmethod',
                'user_hashmethod'
            );
        }

        /**
         * Preload the User Status models in this collection
         *
         * @return user_status_collection
         */
        public function user_status_collection() {
            return $this->_preload_one_to_one(
                'user_status',
                'status_id',
                'user_status'
            );
        }
    }
