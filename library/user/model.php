<?php

    /**
     * User Model
     *
     * @var int $id
     * @var string $email
     * @var binary $password_hash
     * @var int $password_hashmethod
     * @var int $password_cost
     * @var binary $password_salt
     * @var int $status_id
     */
    class user_model extends entity_record_model implements user_definition {

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
         * This function does 4 cache calls (once, the first time its loaded), then the resources are cached.
         * Calling this multiple times will not be resource intensive.
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

            // Don't have any roles? You clearly don't have access.
            if (! ($role_ids = $this->role_ids())) {
                return false;
            }

            // No resources in your roles? (that's weird) You may not continue.
            if (! ($role_resource_ids = $this->role_resource_ids($role_ids))) {
                return false;
            }

            // Check if the resources provided exist in this user's resources, if any are missing, access denied
            foreach ($resource_ids as $resource_id) {
                if (! in_array((int) $resource_id, $role_resource_ids, true)) {
                    return false;
                }
            }

            // Everything looks good, you have access.
            return true;
        }

        /**
         * Is the account active?
         *
         * @return bool
         */
        public function is_active() {
            return isset(core::config()['auth']['login_account_statuses'][$this->status_id]);
        }

        /**
         * This is not a particularly efficient function - it should not be used excessively
         *
         * @param string $resource_name
         *
         * @return bool
         */
        public function has_resource($resource_name) {
            return in_array($resource_name, $this->acl_resource_collection()->field('name'));
        }

        /**
         * Gets an array of role ids this user has
         *
         * @return array ids
         */
        public function role_ids() {
            if (! isset($this->_vars['role_ids'])) {

                // Get user's roles
                $role_ids = entity::dao('user_acl_role')->by_user($this->vars['id']);

                // Get user's groups
                if ($group_ids = entity::dao('acl_group_user')->by_user($this->vars['id'])) {
                    // Get those group's roles
                    foreach (entity::dao('acl_group_role')->by_acl_group_multi($group_ids) as $group_role_ids) {
                        foreach ($group_role_ids as $group_role_id) {
                            $role_ids[] = $group_role_id;
                        }
                    }
                }

                $this->_vars['role_ids'] = array_unique($role_ids);
            }

            return $this->_vars['role_ids'];
        }

        /**
         * Returns a list of resource IDs this user has access to
         *
         * @param array $role_ids
         *
         * @return array
         */
        public function role_resource_ids(array $role_ids = null) {
            // Collect all resources these roles have access to
            if (! isset($this->_vars['role_resource_ids'])) {

                if ($role_ids === null) {
                    $role_ids = $this->role_ids();
                }

                $role_resource_ids = [];
                foreach (entity::dao('acl_role_resource')->by_acl_role_multi($role_ids) as $ids) {
                    if ($ids) {
                        foreach ($ids as $role_resource_id) {
                            $role_resource_ids[] = (int) $role_resource_id;
                        }
                    }
                }

                $this->_vars['role_resource_ids'] = array_unique($role_resource_ids);
            }

            return $this->_vars['role_resource_ids'];
        }

        /**
         * Acl Group Collection
         *
         * @return acl_group_collection
         */
        public function acl_group_collection() {
            if (! array_key_exists('acl_group_collection', $this->_vars)) {
                $this->_vars['acl_group_collection'] = new acl_group_collection(
                    entity::dao('acl_group_user')->by_user($this->vars['id'])
                );
            }
            return $this->_vars['acl_group_collection'];
        }

        /**
         * Auth Collection
         *
         * @param array|null   $order_by array of field names (as the key) and sort direction (entity_record_dao::SORT_ASC, entity_record_dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return auth_collection
         */
        public function auth_collection(array $order_by=null, $offset=null, $limit=null) {
            $key = self::_limit_var_key('auth_collection', $order_by, $offset, $limit);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = new auth_collection(
                    entity::dao('auth')->by_user($this->vars['id'], $order_by, $offset, $limit)
                );
            }
            return $this->_vars[$key];
        }

        /**
         * Acl Role Collection
         *
         * @return acl_role_collection
         */
        public function acl_role_collection() {
            if (! array_key_exists('acl_role_collection', $this->_vars)) {
                $this->_vars['acl_role_collection'] = new acl_role_collection(
                    entity::dao('user_acl_role')->by_user($this->vars['id'])
                );
            }
            return $this->_vars['acl_role_collection'];
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
         * Site Collection
         *
         * @return site_collection
         */
        public function site_collection() {
            if (! array_key_exists('site_collection', $this->_vars)) {
                $this->_vars['site_collection'] = new site_collection(
                    entity::dao('user_site')->by_user($this->vars['id'])
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
