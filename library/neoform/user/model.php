<?php

    namespace neoform\user;

    use neoform\entity;

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
    class model extends entity\record\model implements definition {

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
            return isset(\neoform\core::config()['auth']['login_account_statuses'][$this->status_id]);
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
                $role_ids = entity::dao('user\acl\role')->by_user($this->vars['id']);

                // Get user's groups
                if ($group_ids = entity::dao('acl\group\user')->by_user($this->vars['id'])) {
                    // Get those group's roles
                    foreach (entity::dao('acl\group\role')->by_acl_group_multi($group_ids) as $group_role_ids) {
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
                foreach (entity::dao('acl\role\resource')->by_acl_role_multi($role_ids) as $ids) {
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
         * @param array|null   $order_by array of field names (as the key) and sort direction (entity_record_dao::SORT_ASC, entity_record_dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return \neoform\acl\group\collection
         */
        public function acl_group_collection(array $order_by=null, $offset=null, $limit=null) {
            $key = self::_limit_var_key('acl_group_collection', $order_by, $offset, $limit);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = new \neoform\acl\group\collection(
                    entity::dao('acl\group\user')->by_user($this->vars['id'], $order_by, $offset, $limit)
                );
            }
            return $this->_vars[$key];
        }

        /**
         * Acl Group count
         *
         * @return integer
         */
        public function acl_group_count() {
            $fieldvals = [
                'user_id' => (int) $this->vars['id'],
            ];

            $key = parent::_count_var_key('acl_group_count', $fieldvals);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = entity::dao('acl\group\user')->count($fieldvals);
            }
            return $this->_vars[$key];
        }

        /**
         * Auth Collection
         *
         * @param array|null   $order_by array of field names (as the key) and sort direction (entity_record_dao::SORT_ASC, entity_record_dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return \neoform\auth\collection
         */
        public function auth_collection(array $order_by=null, $offset=null, $limit=null) {
            $key = self::_limit_var_key('auth_collection', $order_by, $offset, $limit);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = new \neoform\auth\collection(
                    entity::dao('auth')->by_user($this->vars['id'], $order_by, $offset, $limit)
                );
            }
            return $this->_vars[$key];
        }

        /**
         * Auth Count
         *
         * @return integer
         */
        public function auth_count() {
            $fieldvals = [
                'user_id' => (int) $this->vars['id'],
            ];

            $key = parent::_count_var_key('auth_count', $fieldvals);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = entity::dao('auth')->count($fieldvals);
            }
            return $this->_vars[$key];
        }

        /**
         * Acl Role Collection
         *
         * @param array|null   $order_by array of field names (as the key) and sort direction (entity_record_dao::SORT_ASC, entity_record_dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return \neoform\acl\role\collection
         */
        public function acl_role_collection(array $order_by=null, $offset=null, $limit=null) {
            $key = self::_limit_var_key('acl_role_collection', $order_by, $offset, $limit);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = new \neoform\acl\role\collection(
                    entity::dao('user\acl\role')->by_user($this->vars['id'], $order_by, $offset, $limit)
                );
            }
            return $this->_vars[$key];
        }

        /**
         * Acl Role count
         *
         * @return integer
         */
        public function acl_role_count() {
            $fieldvals = [
                'user_id' => (int) $this->vars['id'],
            ];

            $key = parent::_count_var_key('acl_role_count', $fieldvals);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = entity::dao('user\acl\role')->count($fieldvals);
            }
            return $this->_vars[$key];
        }

        /**
         * User Date Model based on 'id'
         *
         * @return \neoform\user\date\model
         */
        public function user_date() {
            return $this->_model('user_date', $this->vars['id'], 'user\date\model');
        }

        /**
         * User Lostpassword Model based on 'id'
         *
         * @return \neoform\user\lostpassword\model
         */
        public function user_lostpassword() {
            return $this->_model('user_lostpassword', $this->vars['id'], 'user\lostpassword\model');
        }

        /**
         * Site Collection
         *
         * @param array|null   $order_by array of field names (as the key) and sort direction (entity_record_dao::SORT_ASC, entity_record_dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return \neoform\site\collection
         */
        public function site_collection(array $order_by=null, $offset=null, $limit=null) {
            $key = self::_limit_var_key('site_collection', $order_by, $offset, $limit);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = new \neoform\site\collection(
                    entity::dao('user\site')->by_user($this->vars['id'], $order_by, $offset, $limit)
                );
            }
            return $this->_vars[$key];
        }

        /**
         * Site count
         *
         * @return integer
         */
        public function site_count() {
            $fieldvals = [
                'user_id' => (int) $this->vars['id'],
            ];

            $key = parent::_count_var_key('site_count', $fieldvals);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = entity::dao('user\site')->count($fieldvals);
            }
            return $this->_vars[$key];
        }

        /**
         * User Hashmethod Model based on 'password_hashmethod'
         *
         * @return \neoform\user\hashmethod\model
         */
        public function user_hashmethod() {
            return $this->_model('user_hashmethod', $this->vars['password_hashmethod'], 'user\hashmethod\model');
        }

        /**
         * User Status Model based on 'status_id'
         *
         * @return \neoform\user\status\model
         */
        public function user_status() {
            return $this->_model('user_status', $this->vars['status_id'], 'user\status\model');
        }
    }
