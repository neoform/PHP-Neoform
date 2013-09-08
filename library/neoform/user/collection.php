<?php

    namespace neoform;

    /**
     * User collection
     */
    class user_collection extends entity_record_collection implements user_definition {

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
                'acl_group_user',
                'by_user',
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
                'acl_group_user',
                'user_id'
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
                'auth_collection',
                'auth',
                'by_user',
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Preload the Auth counts
         *
         * @return array counts
         */
        public function auth_count() {
            return $this->_preload_counts(
                'auth_count',
                'auth',
                'user_id'
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
                'user_acl_role',
                'by_user',
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
                'user_acl_role',
                'user_id'
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
                'user_date',
                'id'
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
                'user_lostpassword',
                'id'
            );
        }

        /**
         * Preload the Site models in this collection
         *
         * @param array        $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset   get PKs starting at this offset
         * @param integer|null $limit    max number of PKs to return
         *
         * @return site_collection
         */
        public function site_collection(array $order_by=null, $offset=null, $limit=null) {
            return $this->_preload_many_to_many(
                'site_collection',
                'user_site',
                'by_user',
                'site',
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Preload the Site counts
         *
         * @return array counts
         */
        public function site_count() {
            return $this->_preload_counts(
                'site_count',
                'user_site',
                'user_id'
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
                'user_hashmethod',
                'password_hashmethod'
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
                'user_status',
                'status_id'
            );
        }
    }
