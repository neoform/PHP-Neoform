<?php

    /**
    * User Model
    *
    * @var int $id
    * @var string $email
    * @var string $password_hash
    * @var int $password_hashmethod
    * @var int $password_cost
    * @var string $password_salt
    * @var int $status_id
    */
    class user_model extends record_model implements user_definition {

        public function __get($k) {

            if (isset($this->vars[$k])) {
                switch ($k) {
                    // integers
                    case 'id':
                    case 'password_hashmethod':
                    case 'password_cost':
                    case 'status_id':
                        return (int) $this->vars[$k];

                    // strings
                    case 'email':
                    case 'password_hash':
                    case 'password_salt':
                        return (string) $this->vars[$k];

                    default:
                        return $this->vars[$k];
                }
            }

        }

        /**
         * Auth Collection
         *
         * @return auth_collection
         */
        public function auth_collection() {
            if (! array_key_exists('auth_collection', $this->_vars)) {
                $this->_vars['auth_collection'] = new auth_collection(
                    auth_dao::by_user($this->vars['id'])
                );
            }
            return $this->_vars['auth_collection'];
        }

        /**
         * File Collection
         *
         * @return file_collection
         */
        public function file_collection() {
            if (! array_key_exists('file_collection', $this->_vars)) {
                $this->_vars['file_collection'] = new file_collection(
                    file_dao::by_user($this->vars['id'])
                );
            }
            return $this->_vars['file_collection'];
        }

        /**
         * Folder Collection
         *
         * @return folder_collection
         */
        public function folder_collection() {
            if (! array_key_exists('folder_collection', $this->_vars)) {
                $this->_vars['folder_collection'] = new folder_collection(
                    folder_dao::by_user($this->vars['id'])
                );
            }
            return $this->_vars['folder_collection'];
        }

        /**
         * Merchant Model based on 'id'
         *
         * @return merchant_model
         */
        public function merchant(merchant_model $merchant=null) {
            return $this->_model('merchant', $this->vars['id'], 'merchant_model');
        }

        /**
         * Upload Collection
         *
         * @return upload_collection
         */
        public function upload_collection() {
            if (! array_key_exists('upload_collection', $this->_vars)) {
                $this->_vars['upload_collection'] = new upload_collection(
                    upload_dao::by_user($this->vars['id'])
                );
            }
            return $this->_vars['upload_collection'];
        }

        /**
         * User Date Model based on 'id'
         *
         * @return user_date_model
         */
        public function user_date() {
            return $this->_model('user_date', $this->vars['id'], 'user_date_model');
        }

        /**
         * User Lostpassword Model based on 'id'
         *
         * @return user_lostpassword_model
         */
        public function user_lostpassword() {
            return $this->_model('user_lostpassword', $this->vars['id'], 'user_lostpassword_model');
        }

        /**
         * Permission Collection
         *
         * @return permission_collection
         */
        public function permission_collection() {
            if (! array_key_exists('permission_collection', $this->_vars)) {
                $this->_vars['permission_collection'] = new permission_collection(
                    user_permission_dao::by_user($this->vars['id'])
                );
            }
            return $this->_vars['permission_collection'];
        }

        /**
         * Site Collection
         *
         * @return site_collection
         */
        public function site_collection() {
            if (! array_key_exists('site_collection', $this->_vars)) {
                $this->_vars['site_collection'] = new site_collection(
                    user_site_dao::by_user($this->vars['id'])
                );
            }
            return $this->_vars['site_collection'];
        }

        /**
         * User Hashmethod Model based on 'password_hashmethod'
         *
         * @return user_hashmethod_model
         */
        public function user_hashmethod() {
            return $this->_model('user_hashmethod', $this->vars['password_hashmethod'], 'user_hashmethod_model');
        }

        /**
         * User Status Model based on 'status_id'
         *
         * @return user_status_model
         */
        public function user_status() {
            return $this->_model('user_status', $this->vars['status_id'], 'user_status_model');
        }
    }
