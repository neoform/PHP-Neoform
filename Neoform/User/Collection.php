<?php

    namespace Neoform\User;

    /**
     * User collection
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
                'Neoform\Acl\Group\User',
                'by_user',
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
                'Neoform\Acl\Group\User',
                'user_id'
            );
        }

        /**
         * Preload the Auth models in this collection
         *
         * @param array|null   $order_by array of field names (as the key) and sort direction (Entity\Record_dao::SORT_ASC, Entity\Record_dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return \Neoform\Auth\Collection
         */
        public function auth_collection(array $order_by=null, $offset=null, $limit=null) {
            return $this->_preload_one_to_many(
                'auth_collection',
                'Neoform\Auth',
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
                'Neoform\Auth',
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
         * @return \Neoform\Acl\Role\Collection
         */
        public function acl_role_collection(array $order_by=null, $offset=null, $limit=null) {
            return $this->_preload_many_to_many(
                'acl_role_collection',
                'Neoform\User\Acl\Role',
                'by_user',
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
                'Neoform\User\Acl\Role',
                'user_id'
            );
        }

        /**
         * Preload the User Date models in this collection
         *
         * @return \Neoform\User\Date\Collection
         */
        public function user_date_collection() {
            return $this->_preload_one_to_one(
                'user_date',
                'Neoform\User\Date',
                'id'
            );
        }

        /**
         * Preload the User Lostpassword models in this collection
         *
         * @return \Neoform\User\Lostpassword\Collection
         */
        public function user_lostpassword_collection() {
            return $this->_preload_one_to_one(
                'user_lostpassword',
                'Neoform\User\Lostpassword',
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
         * @return \Neoform\Site\Collection
         */
        public function site_collection(array $order_by=null, $offset=null, $limit=null) {
            return $this->_preload_many_to_many(
                'site_collection',
                'Neoform\User\Site',
                'by_user',
                'Neoform\Site',
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
                'Neoform\User\Site',
                'user_id'
            );
        }

        /**
         * Preload the User Hashmethod models in this collection
         *
         * @return \Neoform\User\Hashmethod\Collection
         */
        public function user_hashmethod_collection() {
            return $this->_preload_one_to_one(
                'user_hashmethod',
                'Neoform\User\Hashmethod',
                'password_hashmethod'
            );
        }

        /**
         * Preload the User Status models in this collection
         *
         * @return \Neoform\User\Status\Collection
         */
        public function user_status_collection() {
            return $this->_preload_one_to_one(
                'user_status',
                'Neoform\User\Status',
                'status_id'
            );
        }
    }
