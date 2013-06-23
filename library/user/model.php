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
         * Checks if this user has the required ACL roles to access a list of resources (array of resource names)
         *
         * @param array $resource_ids
         *
         * @return bool
         */
        public function has_access(array $resource_ids) {

            // No resources needed? You may proceed.
            if (! $resource_ids) {
                return true;
            }

            if (! isset($this->_vars['role_ids'])) {
                $this->_vars['role_ids'] = array_unique(user_acl_role_dao::by_user($this->vars['id']));
            }

            // Don't have any roles? You clearly don't have access.
            if (! $this->_vars['role_ids']) {
                return false;
            }

            // Collect all resources these roles have access to
            if (! isset($this->_vars['role_resource_ids'])) {
                $this->_vars['role_resource_ids'] = [];
                foreach (acl_role_resource_dao::by_acl_role_multi_array($this->_vars['role_ids']) as $ids) {
                    if ($ids) {
                        foreach ($ids as $role_resource_id) {
                            $this->_vars['role_resource_ids'][(int) $role_resource_id] = 1;
                        }
                    }
                }
            }

            // No resources in your roles? (that's weird) You may not continue.
            if (! $this->_vars['role_resource_ids']) {
                return false;
            }

            foreach ($resource_ids as $resource_id) {
                if (! isset($this->_vars['role_resource_ids'][(int) $resource_id])) {
                    return false;
                }
            }

            // Everything looks good, you have access.
            return true;
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
         * Acl Role Collection
         *
         * @return acl_role_collection
         */
        public function acl_role_collection() {
            if (! array_key_exists('acl_role_collection', $this->_vars)) {
                $this->_vars['acl_role_collection'] = new acl_role_collection(
                    user_acl_role_dao::by_user($this->vars['id'])
                );
            }
            return $this->_vars['acl_role_collection'];
        }

        /**
         * Acl Group Collection
         *
         * @return acl_group_collection
         */
        public function acl_group_collection() {
            if (! array_key_exists('acl_group_collection', $this->_vars)) {
                $this->_vars['acl_group_collection'] = new acl_group_collection(
                    acl_group_user_dao::by_user($this->vars['id'])
                );
            }
            return $this->_vars['acl_group_collection'];
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
