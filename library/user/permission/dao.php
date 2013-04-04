<?php

    /**
     * User Permission link DAO
     */
    class user_permission_dao extends link_dao implements user_permission_definition {

        const BY_USER            = 'by_user';
        const BY_USER_PERMISSION = 'by_user_permission';
        const BY_PERMISSION      = 'by_permission';

        // READS

        /**
         * Get permission_id by user_id
         *
         * @param int $user_id
         *
         * @return array result set containing permission_id
         */
        public static function by_user($user_id) {
            return self::_by_fields(
                self::BY_USER,
                [
                    'permission_id',
                ],
                [
                    'user_id' => (int) $user_id,
                ]
            );
        }

        /**
         * Get user_id and permission_id by user_id and permission_id
         *
         * @param int $user_id
         * @param int $permission_id
         *
         * @return array result set containing user_id and permission_id
         */
        public static function by_user_permission($user_id, $permission_id) {
            return self::_by_fields(
                self::BY_USER_PERMISSION,
                [
                    'user_id',
                    'permission_id',
                ],
                [
                    'user_id'       => (int) $user_id,
                    'permission_id' => (int) $permission_id,
                ]
            );
        }

        /**
         * Get user_id by permission_id
         *
         * @param int $permission_id
         *
         * @return array result set containing user_id
         */
        public static function by_permission($permission_id) {
            return self::_by_fields(
                self::BY_PERMISSION,
                [
                    'user_id',
                ],
                [
                    'permission_id' => (int) $permission_id,
                ]
            );
        }

        /**
         * Get multiple sets of user_id by permission_id
         *
         * @param permission_collection $permission_collection
         *
         * @return array of result sets containing user_id
         */
        public static function by_permission_multi(permission_collection $permission_collection) {
            $keys = [];
            foreach ($permission_collection as $k => $permission) {
                $keys[$k] = [
                    'permission_id' => (int) $permission->id,
                ];
            }

            return self::_by_fields_multi(
                self::BY_PERMISSION,
                [
                    'user_id',
                ],
                $keys
            );
        }

        /**
         * Get multiple sets of permission_id by user_id
         *
         * @param user_collection $user_collection
         *
         * @return array of result sets containing permission_id
         */
        public static function by_user_multi(user_collection $user_collection) {
            $keys = [];
            foreach ($user_collection as $k => $user) {
                $keys[$k] = [
                    'user_id' => (int) $user->id,
                ];
            }

            return self::_by_fields_multi(
                self::BY_USER,
                [
                    'permission_id',
                ],
                $keys
            );
        }

        // WRITES

        /**
         * Insert User Permission link, created from an array of $info
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

            // BY_USER_PERMISSION
            if (array_key_exists('user_id', $info) && array_key_exists('permission_id', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_USER_PERMISSION,
                        [
                            'user_id'       => (int) $info['user_id'],
                            'permission_id' => (int) $info['permission_id'],
                        ]
                    )
                );
            }

            // BY_PERMISSION
            if (array_key_exists('permission_id', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_PERMISSION,
                        [
                            'permission_id' => (int) $info['permission_id'],
                        ]
                    )
                );
            }

            return $return;
        }

        /**
         * Insert multiple User Permission links, created from an array of arrays of $info
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

                // BY_USER_PERMISSION
                if (array_key_exists('user_id', $info) && array_key_exists('permission_id', $info)) {
                    parent::_cache_delete(
                        parent::_build_key(
                            self::BY_USER_PERMISSION,
                            [
                                'user_id'       => (int) $info['user_id'],
                                'permission_id' => (int) $info['permission_id'],
                            ]
                        )
                    );
                }

                // BY_PERMISSION
                if (array_key_exists('permission_id', $info)) {
                    parent::_cache_delete(
                        parent::_build_key(
                            self::BY_PERMISSION,
                            [
                                'permission_id' => (int) $info['permission_id'],
                            ]
                        )
                    );
                }

            }

            return $return;
        }

        /**
         * Update User Permission link records based on $where inputs
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

            // BY_USER_PERMISSION
            if (array_key_exists('user_id', $new_info) && array_key_exists('permission_id', $new_info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_USER_PERMISSION,
                        [
                            'user_id'       => (int) $new_info['user_id'],
                            'permission_id' => (int) $new_info['permission_id'],
                        ]
                    )
                );
            }
            if (array_key_exists('user_id', $where) && array_key_exists('permission_id', $where)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_USER_PERMISSION,
                        [
                            'user_id'       => (int) $where['user_id'],
                            'permission_id' => (int) $where['permission_id'],
                        ]
                    )
                );
            }

            // BY_PERMISSION
            if (array_key_exists('permission_id', $new_info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_PERMISSION,
                        [
                            'permission_id' => (int) $new_info['permission_id'],
                        ]
                    )
                );
            }
            if (array_key_exists('permission_id', $where)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_PERMISSION,
                        [
                            'permission_id' => (int) $where['permission_id'],
                        ]
                    )
                );
            }

            return $return;
        }

        /**
         * Delete multiple User Permission link records based on an array of associative arrays
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
            // BY_USER_PERMISSION
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_USER_PERMISSION,
                    [
                        'user_id'       => (int) $keys['user_id'],
                        'permission_id' => (int) $keys['permission_id'],
                    ]
                )
            );
            // BY_PERMISSION
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_PERMISSION,
                    [
                        'permission_id' => (int) $keys['permission_id'],
                    ]
                )
            );
            return $return;
        }

        /**
         * Delete multiple sets of User Permission link records based on an array of associative arrays
         *
         * @param array $keys_arr an array of arrays, keys match the column names
         *
         * @return bool
         */
        public static function deletes(array $keys_arr) {
            $return = parent::_deletes($keys_arr);

            // PRIMARY KEYS
            foreach ($keys_arr as $keys) {
                $unique_user_id_arr[(int) $keys['user_id']] = (int) $keys['user_id'];
                $unique_permission_id_arr[(int) $keys['permission_id']] = (int) $keys['permission_id'];

                // BY_USER_PERMISSION
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_USER_PERMISSION,
                        [
                            'user_id'       => (int) $keys['user_id'],
                            'permission_id' => (int) $keys['permission_id'],
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
            // BY_PERMISSION
            foreach ($unique_permission_id_arr as $permission_id) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_PERMISSION,
                        [
                            'permission_id' => (int) $permission_id,
                        ]
                    )
                );
            }
            return $return;
        }
    }
