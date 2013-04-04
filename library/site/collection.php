<?php

    /**
     * User collection
     */
    class user_collection extends record_collection implements user_definition {

        /**
         * Preload the Auth models in this collection
         *
         * @return auth_collection
         */
        public function auth_collection() {
            return $this->_preload_one_to_many('auth', 'by_user');
        }

        /**
         * Preload the User Date models in this collection
         *
         * @return user_date_collection
         */
        public function user_date_collection() {
            return $this->_preload_one_to_one('user_date', 'id');
        }

        /**
         * Preload the User Lostpassword models in this collection
         *
         * @return user_lostpassword_collection
         */
        public function user_lostpassword_collection() {
            return $this->_preload_one_to_one('user_lostpassword', 'id');
        }

        /**
         * Preload the Permission models in this collection
         *
         * @return permission_collection
         */
        public function permission_collection() {
            return $this->_preload_many_to_many('user_permission', 'by_user', 'permission');
        }

        /**
         * Preload the Site models in this collection
         *
         * @return site_collection
         */
        public function site_collection() {
            return $this->_preload_many_to_many('user_site', 'by_user', 'site');
        }

        /**
         * Preload the User Hashmethod models in this collection
         *
         * @return user_hashmethod_collection
         */
        public function user_hashmethod_collection() {
            return $this->_preload_one_to_one('user_hashmethod', 'password_hashmethod');
        }

        /**
         * Preload the User Status models in this collection
         *
         * @return user_status_collection
         */
        public function user_status_collection() {
            return $this->_preload_one_to_one('user_status', 'status_id');
        }

    }
