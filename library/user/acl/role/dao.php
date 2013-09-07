<?php

    /**
     * User Acl Role link DAO
     */
    class user_acl_role_dao extends entity_link_dao implements user_acl_role_definition {

        const BY_USER          = 'by_user';
        const BY_USER_ACL_ROLE = 'by_user_acl_role';
        const BY_ACL_ROLE      = 'by_acl_role';

        /**
         * $var array $field_bindings list of fields and their corresponding bindings
         *
         * @return array
         */
        protected $field_bindings = [
            'user_id'     => self::TYPE_INTEGER,
            'acl_role_id' => self::TYPE_INTEGER,
        ];

        // READS

        /**
         * Get acl_role_id by user_id
         *
         * @param int $user_id
         * @param array|null $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get rows starting at this offset
         * @param integer|null $limit max number of rows to return
         *
         * @return array result set containing acl_role_id
         */
        public function by_user($user_id, array $order_by=null, $offset=null, $limit=null) {
            return parent::_by_fields(
                self::BY_USER,
                [
                    'acl_role_id',
                ],
                [
                    'user_id' => (int) $user_id,
                ],
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Get user_id and acl_role_id by user_id and acl_role_id
         *
         * @param int $user_id
         * @param int $acl_role_id
         * @param array|null $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get rows starting at this offset
         * @param integer|null $limit max number of rows to return
         *
         * @return array result set containing user_id and acl_role_id
         */
        public function by_user_acl_role($user_id, $acl_role_id, array $order_by=null, $offset=null, $limit=null) {
            return parent::_by_fields(
                self::BY_USER_ACL_ROLE,
                [
                    'user_id',
                    'acl_role_id',
                ],
                [
                    'user_id'     => (int) $user_id,
                    'acl_role_id' => (int) $acl_role_id,
                ],
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Get user_id by acl_role_id
         *
         * @param int $acl_role_id
         * @param array|null $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get rows starting at this offset
         * @param integer|null $limit max number of rows to return
         *
         * @return array result set containing user_id
         */
        public function by_acl_role($acl_role_id, array $order_by=null, $offset=null, $limit=null) {
            return parent::_by_fields(
                self::BY_ACL_ROLE,
                [
                    'user_id',
                ],
                [
                    'acl_role_id' => (int) $acl_role_id,
                ],
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Get multiple sets of acl_role_id by a collection of users
         *
         * @param user_collection|array $user_list
         * @param array|null $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get rows starting at this offset
         * @param integer|null $limit max number of rows to return
         *
         * @return array of result sets containing acl_role_id
         */
        public function by_user_multi($user_list, array $order_by=null, $offset=null, $limit=null) {
            $keys = [];
            if ($user_list instanceof user_collection) {
                foreach ($user_list as $k => $user) {
                    $keys[$k] = [
                        'user_id' => (int) $user->id,
                    ];
                }

            } else {
                foreach ($user_list as $k => $user) {
                    $keys[$k] = [
                        'user_id' => (int) $user,
                    ];
                }

            }

            return parent::_by_fields_multi(
                self::BY_USER,
                [
                    'acl_role_id',
                ],
                $keys,
                $order_by,
                $offset,
                $limit
            );
        }

        /**
         * Get multiple sets of user_id by a collection of acl_roles
         *
         * @param acl_role_collection|array $acl_role_list
         * @param array|null $order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)
         * @param integer|null $offset get rows starting at this offset
         * @param integer|null $limit max number of rows to return
         *
         * @return array of result sets containing user_id
         */
        public function by_acl_role_multi($acl_role_list, array $order_by=null, $offset=null, $limit=null) {
            $keys = [];
            if ($acl_role_list instanceof acl_role_collection) {
                foreach ($acl_role_list as $k => $acl_role) {
                    $keys[$k] = [
                        'acl_role_id' => (int) $acl_role->id,
                    ];
                }

            } else {
                foreach ($acl_role_list as $k => $acl_role) {
                    $keys[$k] = [
                        'acl_role_id' => (int) $acl_role,
                    ];
                }

            }

            return parent::_by_fields_multi(
                self::BY_ACL_ROLE,
                [
                    'user_id',
                ],
                $keys,
                $order_by,
                $offset,
                $limit
            );
        }

        // WRITES

        /**
         * Insert User Acl Role link, created from an array of $info
         *
         * @param array $info associative array, keys matching columns in database for this entity
         *
         * @return boolean
         */
        public function insert(array $info) {

            return parent::_insert($info);
        }

        /**
         * Insert multiple User Acl Role links, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return boolean
         */
        public function insert_multi(array $infos) {

            return parent::_insert_multi($infos);
        }

        /**
         * Update User Acl Role link records based on $where inputs
         *
         * @param array $new_info the new link record data
         * @param array $where associative array, matching columns with values
         *
         * @return bool
         */
        public function update(array $new_info, array $where) {

            // Update link
            return parent::_update($new_info, $where);

        }

        /**
         * Delete multiple User Acl Role link records based on an array of associative arrays
         *
         * @param array $keys keys match the column names
         *
         * @return bool
         */
        public function delete(array $keys) {

            // Delete link
            return parent::_delete($keys);

        }

        /**
         * Delete multiple sets of User Acl Role link records based on an array of associative arrays
         *
         * @param array $keys_arr an array of arrays, keys match the column names
         *
         * @return bool
         */
        public function delete_multi(array $keys_arr) {

            // Delete links
            return parent::_delete_multi($keys_arr);

        }
    }
