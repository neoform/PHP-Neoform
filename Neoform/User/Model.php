<?php

    namespace Neoform\User;

    use Neoform\Entity;
    use Neoform;

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
    class Model extends Entity\Record\Model {

        // Load entity details into the class
        use Details;

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

        public function get($k) {

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
        public function hasAccess(array $resource_ids) {

            // No resources needed? You may proceed.
            if (! $resource_ids) {
                return true;
            }

            // Don't have any roles? You clearly don't have access.
            if (! $this->role_ids()) {
                return false;
            }

            // No resources in your roles? (that's weird) You may not continue.
            if (! ($role_resource_ids = $this->role_resource_ids())) {
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
            $statuses = Neoform\Auth\Config::get()->getLoginAccountStatuses();
            return isset($statuses[$this->status_id]);
        }

        /**
         * This is not a particularly efficient function - it should not be used excessively
         *
         * @param string $resource_name
         *
         * @return bool
         */
        public function hasResource($resource_name) {
            if (! isset($this->_vars['hasResource'][$resource_name])) {
                try {
                    $this->_vars['hasResource'][$resource_name] = (bool) in_array(
                        Neoform\Acl\Resource\Lib::id_from_slug($resource_name),
                        $this->role_resource_ids()
                    );
                } catch (Neoform\Acl\Resource\Exception $e) {
                    Neoform\Core::log($e->getMessage());
                    return false;
                }
            }

            return $this->_vars['hasResource'][$resource_name];
        }

        /**
         * This is not a particularly efficient function - it should not be used excessively
         *
         * @param string $mode
         * @param array  $resource_names
         *
         * @return bool
         * @throws exception
         */
        public function hasResources($mode, array $resource_names) {
            switch ((string) $mode) {
                case 'all':
                    foreach ($resource_names as $resource_name) {
                        if (! $this->hasResource($resource_name)) {
                            return false;
                        }
                    }

                    return true;

                case 'any':
                    foreach ($resource_names as $resource_name) {
                        if ($this->hasResource($resource_name)) {
                            return true;
                        }
                    }

                    return false;
            }

            throw new Exception('unknown mode');
        }

        /**
         * Gets an array of role ids this user has
         *
         * @return array ids
         */
        public function role_ids() {
            if (! isset($this->_vars['role_ids'])) {

                // Get user's roles
                $role_ids = Neoform\User\Acl\Role\Dao::get()->by_user($this->vars['id']);

                // Get user's groups
                if ($group_ids = Neoform\Acl\Group\User\Dao::get()->by_user($this->vars['id'])) {
                    // Get those group's roles
                    foreach (Neoform\Acl\Group\Role\Dao::get()->by_acl_group_multi($group_ids) as $group_role_ids) {
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
         * @return array
         */
        public function role_resource_ids() {
            // Collect all resources these roles have access to
            if (! isset($this->_vars['role_resource_ids'])) {

                $role_ids = $this->role_ids();

                $role_resource_ids = [];
                foreach (Neoform\Acl\Role\Resource\Dao::get()->by_acl_role_multi($role_ids) as $ids) {
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
         * @param array|null   $order_by array of field names (as the key) and sort direction (Entity\Record\Dao::SORT_ASC, Entity\Record\Dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return \Neoform\Acl\Group\Collection
         */
        public function acl_group_collection(array $order_by=null, $offset=null, $limit=null) {
            $key = self::_limitVarKey('acl_group_collection', $order_by, $offset, $limit);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = Neoform\Acl\Group\Collection::fromPks(
                    Neoform\Acl\Group\User\Dao::get()->by_user($this->vars['id'], $order_by, $offset, $limit)
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

            $key = parent::_countVarKey('acl_group_count', $fieldvals);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = Neoform\Acl\Group\User\Dao::get()->count($fieldvals);
            }
            return $this->_vars[$key];
        }

        /**
         * Auth Collection
         *
         * @param array|null   $order_by array of field names (as the key) and sort direction (Entity\Record\Dao::SORT_ASC, Entity\Record\Dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return \Neoform\Auth\Collection
         */
        public function auth_collection(array $order_by=null, $offset=null, $limit=null) {
            $key = self::_limitVarKey('auth_collection', $order_by, $offset, $limit);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = Neoform\Auth\Collection::fromPks(
                    Neoform\Auth\Dao::get()->by_user($this->vars['id'], $order_by, $offset, $limit)
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

            $key = parent::_countVarKey('auth_count', $fieldvals);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = Neoform\Auth\Dao::get()->count($fieldvals);
            }
            return $this->_vars[$key];
        }

        /**
         * Acl Role Collection
         *
         * @param array|null   $order_by array of field names (as the key) and sort direction (Entity\Record\Dao::SORT_ASC, Entity\Record\Dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return \Neoform\Acl\Role\Collection
         */
        public function acl_role_collection(array $order_by=null, $offset=null, $limit=null) {
            $key = self::_limitVarKey('acl_role_collection', $order_by, $offset, $limit);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = Neoform\Acl\Role\Collection::fromPks(
                    Neoform\User\Acl\Role\Dao::get()->by_user($this->vars['id'], $order_by, $offset, $limit)
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

            $key = parent::_countVarKey('acl_role_count', $fieldvals);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = Neoform\User\Acl\Role\Dao::get()->count($fieldvals);
            }
            return $this->_vars[$key];
        }

        /**
         * User Date Model based on 'id'
         *
         * @return \Neoform\User\Date\Model
         */
        public function user_date() {
            return $this->_model('user_date', $this->vars['id'], 'Neoform\User\Date\Model');
        }

        /**
         * User Lostpassword Model based on 'id'
         *
         * @return \Neoform\User\Lostpassword\Model
         */
        public function user_lostpassword() {
            return $this->_model('user_lostpassword', $this->vars['id'], 'Neoform\User\Lostpassword\Model');
        }

        /**
         * Site Collection
         *
         * @param array|null   $order_by array of field names (as the key) and sort direction (Entity\Record\Dao::SORT_ASC, Entity\Record\Dao::SORT_DESC)
         * @param integer|null $offset get PKs starting at this offset
         * @param integer|null $limit max number of PKs to return
         *
         * @return \Neoform\Site\Collection
         */
        public function site_collection(array $order_by=null, $offset=null, $limit=null) {
            $key = self::_limitVarKey('site_collection', $order_by, $offset, $limit);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = \Neoform\Site\Collection::fromPks(
                    Neoform\User\Site\Dao::get()->by_user($this->vars['id'], $order_by, $offset, $limit)
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

            $key = parent::_countVarKey('site_count', $fieldvals);
            if (! array_key_exists($key, $this->_vars)) {
                $this->_vars[$key] = Neoform\User\Site\Dao::get()->count($fieldvals);
            }
            return $this->_vars[$key];
        }

        /**
         * User Hashmethod Model based on 'password_hashmethod'
         *
         * @return \Neoform\User\Hashmethod\Model
         */
        public function user_hashmethod() {
            return $this->_model('user_hashmethod', $this->vars['password_hashmethod'], 'Neoform\User\Hashmethod\Model');
        }

        /**
         * User Status Model based on 'status_id'
         *
         * @return \Neoform\User\Status\Model
         */
        public function user_status() {
            return $this->_model('user_status', $this->vars['status_id'], 'Neoform\User\Status\Model');
        }
    }
