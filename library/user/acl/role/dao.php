<?php

    /**
     * User Acl Role link DAO
     */
    class user_acl_role_dao extends link_dao implements user_acl_role_definition {

        const BY_USER      = 'by_user';
        const BY_USER_ROLE = 'by_user_role';
        const BY_ROLE      = 'by_role';

        /**
         * Get the generic bindings of the table columns
         *
         * @return array
         */
        public static function bindings() {
            return [
                'user_id' => 'int',
                'role_id' => 'int',
            ];
        }


        // READS

        /**
         * Get role_id by user_id
         *
         * @param int $user_id
         *
         * @return array result set containing role_id
         */
        public static function by_user($user_id) {
            return self::_by_fields(
                self::BY_USER,
                [
                    'role_id',
                ],
                [
                    'user_id' => (int) $user_id,
                ]
            );
        }

        /**
         * Get user_id and role_id by user_id and role_id
         *
         * @param int $user_id
         * @param int $role_id
         *
         * @return array result set containing user_id and role_id
         */
        public static function by_user_role($user_id, $role_id) {
            return self::_by_fields(
                self::BY_USER_ROLE,
                [
                    'user_id',
                    'role_id',
                ],
                [
                    'user_id' => (int) $user_id,
                    'role_id' => (int) $role_id,
                ]
            );
        }

        /**
         * Get user_id by role_id
         *
         * @param int $role_id
         *
         * @return array result set containing user_id
         */
        public static function by_role($role_id) {
            return self::_by_fields(
                self::BY_ROLE,
                [
                    'user_id',
                ],
                [
                    'role_id' => (int) $role_id,
                ]
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
        public static function insert(array $info) {
            $return = parent::_insert($info);

            // Delete Cache
            // BY_USER
            if (array_key_exists('user_id', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_USER,
                        [
                            'user_id' => (int) $info['user_id'],
                        ]
                    )
                );
            }

            // BY_USER_ROLE
            if (array_key_exists('user_id', $info) && array_key_exists('role_id', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_USER_ROLE,
                        [
                            'user_id' => (int) $info['user_id'],
                            'role_id' => (int) $info['role_id'],
                        ]
                    )
                );
            }

            // BY_ROLE
            if (array_key_exists('role_id', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_ROLE,
                        [
                            'role_id' => (int) $info['role_id'],
                        ]
                    )
                );
            }

            return $return;
        }

        /**
         * Insert multiple User Acl Role links, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return boolean
         */
        public static function inserts(array $infos) {
            $return = parent::_inserts($infos);

            // Delete Cache
            foreach ($infos as $info) {
                // BY_USER
                if (array_key_exists('user_id', $info)) {
                    parent::_cache_delete(
                        parent::_build_key(
                            self::BY_USER,
                            [
                                'user_id' => (int) $info['user_id'],
                            ]
                        )
                    );
                }

                // BY_USER_ROLE
                if (array_key_exists('user_id', $info) && array_key_exists('role_id', $info)) {
                    parent::_cache_delete(
                        parent::_build_key(
                            self::BY_USER_ROLE,
                            [
                                'user_id' => (int) $info['user_id'],
                                'role_id' => (int) $info['role_id'],
                            ]
                        )
                    );
                }

                // BY_ROLE
                if (array_key_exists('role_id', $info)) {
                    parent::_cache_delete(
                        parent::_build_key(
                            self::BY_ROLE,
                            [
                                'role_id' => (int) $info['role_id'],
                            ]
                        )
                    );
                }
            }

            return $return;
        }

        /**
         * Update User Acl Role link records based on $where inputs
         *
         * @param array $new_info the new link record data
         * @param array $where associative array, matching columns with values
         *
         * @return bool
         */
        public static function update(array $new_info, array $where) {
            $return = parent::_update($new_info, $where);

            // Delete Cache
            // BY_USER
            if (array_key_exists('user_id', $new_info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_USER,
                        [
                            'user_id' => (int) $new_info['user_id'],
                        ]
                    )
                );
            }
            if (array_key_exists('user_id', $where)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_USER,
                        [
                            'user_id' => (int) $where['user_id'],
                        ]
                    )
                );
            }

            // BY_USER_ROLE
            if (array_key_exists('user_id', $new_info) && array_key_exists('role_id', $new_info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_USER_ROLE,
                        [
                            'user_id' => (int) $new_info['user_id'],
                            'role_id' => (int) $new_info['role_id'],
                        ]
                    )
                );
            }
            if (array_key_exists('user_id', $where) && array_key_exists('role_id', $where)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_USER_ROLE,
                        [
                            'user_id' => (int) $where['user_id'],
                            'role_id' => (int) $where['role_id'],
                        ]
                    )
                );
            }

            // BY_ROLE
            if (array_key_exists('role_id', $new_info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_ROLE,
                        [
                            'role_id' => (int) $new_info['role_id'],
                        ]
                    )
                );
            }
            if (array_key_exists('role_id', $where)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_ROLE,
                        [
                            'role_id' => (int) $where['role_id'],
                        ]
                    )
                );
            }

            return $return;
        }

        /**
         * Delete multiple User Acl Role link records based on an array of associative arrays
         *
         * @param array $keys keys match the column names
         *
         * @return bool
         */
        public static function delete(array $keys) {
            $return = parent::_delete($keys);

            // Delete Cache
            // BY_USER
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_USER,
                    [
                        'user_id' => (int) $keys['user_id'],
                    ]
                )
            );

            // BY_USER_ROLE
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_USER_ROLE,
                    [
                        'user_id' => (int) $keys['user_id'],
                        'role_id' => (int) $keys['role_id'],
                    ]
                )
            );

            // BY_ROLE
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_ROLE,
                    [
                        'role_id' => (int) $keys['role_id'],
                    ]
                )
            );

            return $return;
        }

        /**
         * Delete multiple sets of User Acl Role link records based on an array of associative arrays
         *
         * @param array $keys_arr an array of arrays, keys match the column names
         *
         * @return bool
         */
        public static function deletes(array $keys_arr) {
            $return = parent::_deletes($keys_arr);

            // PRIMARY KEYS
            $unique_user_id_arr = [];
            $unique_role_id_arr = [];
            foreach ($keys_arr as $keys) {
                $unique_user_id_arr[(int) $keys['user_id']] = (int) $keys['user_id'];
                $unique_role_id_arr[(int) $keys['role_id']] = (int) $keys['role_id'];

                // BY_USER_ROLE
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_USER_ROLE,
                        [
                            'user_id' => (int) $keys['user_id'],
                            'role_id' => (int) $keys['role_id'],
                        ]
                    )
                );
            }

            // BY_USER
            foreach ($unique_user_id_arr as $user_id) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_USER,
                        [
                            'user_id' => (int) $user_id,
                        ]
                    )
                );
            }

            // BY_ROLE
            foreach ($unique_role_id_arr as $role_id) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_ROLE,
                        [
                            'role_id' => (int) $role_id,
                        ]
                    )
                );
            }

            return $return;
        }
    }
