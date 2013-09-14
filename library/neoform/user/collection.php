<?php

    namespace neoform\user;

    /**
     * User collection
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
                'acl\group\user',
                'by_user',
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
                'acl\group\user',
                'user_id'
            );
        }

        /**
         * Preload the Auth models in this collection
         *
         * @param array|null   $order_by array of field names (as the key) and sort direction (entity\record_dao::SORT_ASC, entity\record_dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return \neoform\auth\collection
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
         * @return \neoform\acl\role\collection
         */
        public function acl_role_collection(array $order_by=null, $offset=null, $limit=null) {
            return $this->_preload_many_to_many(
                'acl_role_collection',
                'user\acl\role',
                'by_user',
                'acl\role',
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
                'user\acl\role',
                'user_id'
            );
        }

        /**
         * Preload the User Date models in this collection
         *
         * @return \neoform\user\date\collection
         */
        public function user_date_collection() {
            return $this->_preload_one_to_one(
                'user_date',
                'user\date',
                'id'
            );
        }

        /**
         * Preload the User Lostpassword models in this collection
         *
         * @return \neoform\user\lostpassword\collection
         */
        public function user_lostpassword_collection() {
            return $this->_preload_one_to_one(
                'user_lostpassword',
                'user\lostpassword',
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
         * @return \neoform\site\collection
         */
        public function site_collection(array $order_by=null, $offset=null, $limit=null) {
            return $this->_preload_many_to_many(
                'site_collection',
                'user\site',
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
                'user\site',
                'user_id'
            );
        }

        /**
         * Preload the User Hashmethod models in this collection
         *
         * @return \neoform\user\hashmethod\collection
         */
        public function user_hashmethod_collection() {
            return $this->_preload_one_to_one(
                'user_hashmethod',
                'user\hashmethod',
                'password_hashmethod'
            );
        }

        /**
         * Preload the User Status models in this collection
         *
         * @return \neoform\user\status\collection
         */
        public function user_status_collection() {
            return $this->_preload_one_to_one(
                'user_status',
                'user\status',
                'status_id'
            );
        }
    }
