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
         * @param auth_collection $auth_collection preload collection
         *
         * @return auth_collection
         */
        public function auth_collection(auth_collection $auth_collection=null) {
            if (! array_key_exists('auth_collection', $this->_vars)) {
                if ($auth_collection !== null) {
                    $this->_vars['auth_collection'] = $auth_collection;
                } else {
                    $this->_vars['auth_collection'] = new auth_collection(
                        auth_dao::by_user($this->vars['id'])
                    );
                }
            }
            return $this->_vars['auth_collection'];
        }

        /**
         * File Collection
         *
         * @param file_collection $file_collection preload collection
         *
         * @return file_collection
         */
        public function file_collection(file_collection $file_collection=null) {
            if (! array_key_exists('file_collection', $this->_vars)) {
                if ($file_collection !== null) {
                    $this->_vars['file_collection'] = $file_collection;
                } else {
                    $this->_vars['file_collection'] = new file_collection(
                        file_dao::by_user($this->vars['id'])
                    );
                }
            }
            return $this->_vars['file_collection'];
        }

        /**
         * Folder Collection
         *
         * @param folder_collection $folder_collection preload collection
         *
         * @return folder_collection
         */
        public function folder_collection(folder_collection $folder_collection=null) {
            if (! array_key_exists('folder_collection', $this->_vars)) {
                if ($folder_collection !== null) {
                    $this->_vars['folder_collection'] = $folder_collection;
                } else {
                    $this->_vars['folder_collection'] = new folder_collection(
                        folder_dao::by_user($this->vars['id'])
                    );
                }
            }
            return $this->_vars['folder_collection'];
        }

        /**
         * Merchant Model based on 'id'
         *
         * @param merchant_model $merchant preload model
         *
         * @return merchant_model
         */
        public function merchant(merchant_model $merchant=null) {
            return $merchant !== null ? ($this->_vars['merchant'] = $merchant) : $this->_model('merchant', $this->vars['id'], 'merchant_model');
        }

        /**
         * Upload Collection
         *
         * @param upload_collection $upload_collection preload collection
         *
         * @return upload_collection
         */
        public function upload_collection(upload_collection $upload_collection=null) {
            if (! array_key_exists('upload_collection', $this->_vars)) {
                if ($upload_collection !== null) {
                    $this->_vars['upload_collection'] = $upload_collection;
                } else {
                    $this->_vars['upload_collection'] = new upload_collection(
                        upload_dao::by_user($this->vars['id'])
                    );
                }
            }
            return $this->_vars['upload_collection'];
        }

        /**
         * User Date Model based on 'id'
         *
         * @param user_date_model $user_date preload model
         *
         * @return user_date_model
         */
        public function user_date(user_date_model $user_date=null) {
            return $user_date !== null ? ($this->_vars['user_date'] = $user_date) : $this->_model('user_date', $this->vars['id'], 'user_date_model');
        }

        /**
         * User Lostpassword Model based on 'id'
         *
         * @param user_lostpassword_model $user_lostpassword preload model
         *
         * @return user_lostpassword_model
         */
        public function user_lostpassword(user_lostpassword_model $user_lostpassword=null) {
            return $user_lostpassword !== null ? ($this->_vars['user_lostpassword'] = $user_lostpassword) : $this->_model('user_lostpassword', $this->vars['id'], 'user_lostpassword_model');
        }

        /**
         * Permission Collection
         *
         * @param permission_collection $permission_collection preload collection
         *
         * @return permission_collection
         */
        public function permission_collection(permission_collection $permission_collection=null) {
            if (! array_key_exists('permission_collection', $this->_vars)) {
                if ($permission_collection !== null) {
                    $this->_vars['permission_collection'] = $permission_collection;
                } else {
                    $this->_vars['permission_collection'] = new permission_collection(
                        user_permission_dao::by_user($this->vars['id'])
                    );
                }
            }
            return $this->_vars['permission_collection'];
        }

        /**
         * Site Collection
         *
         * @param site_collection $site_collection preload collection
         *
         * @return site_collection
         */
        public function site_collection(site_collection $site_collection=null) {
            if (! array_key_exists('site_collection', $this->_vars)) {
                if ($site_collection !== null) {
                    $this->_vars['site_collection'] = $site_collection;
                } else {
                    $this->_vars['site_collection'] = new site_collection(
                        user_site_dao::by_user($this->vars['id'])
                    );
                }
            }
            return $this->_vars['site_collection'];
        }

        /**
         * User Hashmethod Model based on 'password_hashmethod'
         *
         * @param user_hashmethod_model $user_hashmethod preload model
         *
         * @return user_hashmethod_model
         */
        public function user_hashmethod(user_hashmethod_model $user_hashmethod=null) {
            return $user_hashmethod !== null ? ($this->_vars['user_hashmethod'] = $user_hashmethod) : $this->_model('user_hashmethod', $this->vars['password_hashmethod'], 'user_hashmethod_model');
        }

        /**
         * User Status Model based on 'status_id'
         *
         * @param user_status_model $user_status preload model
         *
         * @return user_status_model
         */
        public function user_status(user_status_model $user_status=null) {
            return $user_status !== null ? ($this->_vars['user_status'] = $user_status) : $this->_model('user_status', $this->vars['status_id'], 'user_status_model');
        }
    }
