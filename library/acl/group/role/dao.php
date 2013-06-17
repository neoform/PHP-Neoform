<?php

    /**
     * Acl Group Role link DAO
     */
    class acl_group_role_dao extends link_dao implements acl_group_role_definition {

        const BY_ACL_GROUP          = 'by_acl_group';
        const BY_ACL_GROUP_ACL_ROLE = 'by_acl_group_acl_role';
        const BY_ACL_ROLE           = 'by_acl_role';

        /**
         * Get the generic bindings of the table columns
         *
         * @return array
         */
        public static function bindings() {
            return [
                'acl_group_id' => 'int',
                'acl_role_id'  => 'int',
            ];
        }


        // READS

        /**
         * Get acl_role_id by acl_group_id
         *
         * @param int $acl_group_id
         *
         * @return array result set containing acl_role_id
         */
        public static function by_acl_group($acl_group_id) {
            return self::_by_fields(
                self::BY_ACL_GROUP,
                [
                    'acl_role_id',
                ],
                [
                    'acl_group_id' => (int) $acl_group_id,
                ]
            );
        }

        /**
         * Get acl_group_id and acl_role_id by acl_group_id and acl_role_id
         *
         * @param int $acl_group_id
         * @param int $acl_role_id
         *
         * @return array result set containing acl_group_id and acl_role_id
         */
        public static function by_acl_group_acl_role($acl_group_id, $acl_role_id) {
            return self::_by_fields(
                self::BY_ACL_GROUP_ACL_ROLE,
                [
                    'acl_group_id',
                    'acl_role_id',
                ],
                [
                    'acl_group_id' => (int) $acl_group_id,
                    'acl_role_id'  => (int) $acl_role_id,
                ]
            );
        }

        /**
         * Get acl_group_id by acl_role_id
         *
         * @param int $acl_role_id
         *
         * @return array result set containing acl_group_id
         */
        public static function by_acl_role($acl_role_id) {
            return self::_by_fields(
                self::BY_ACL_ROLE,
                [
                    'acl_group_id',
                ],
                [
                    'acl_role_id' => (int) $acl_role_id,
                ]
            );
        }

        // WRITES

        /**
         * Insert Acl Group Role link, created from an array of $info
         *
         * @param array $info associative array, keys matching columns in database for this entity
         *
         * @return boolean
         */
        public static function insert(array $info) {
            $return = parent::_insert($info);

            // Delete Cache
            // BY_ACL_GROUP
            if (array_key_exists('acl_group_id', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_ACL_GROUP,
                        [
                            'acl_group_id' => (int) $info['acl_group_id'],
                        ]
                    )
                );
            }

            // BY_ACL_GROUP_ACL_ROLE
            if (array_key_exists('acl_group_id', $info) && array_key_exists('acl_role_id', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_ACL_GROUP_ACL_ROLE,
                        [
                            'acl_group_id' => (int) $info['acl_group_id'],
                            'acl_role_id'  => (int) $info['acl_role_id'],
                        ]
                    )
                );
            }

            // BY_ACL_ROLE
            if (array_key_exists('acl_role_id', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_ACL_ROLE,
                        [
                            'acl_role_id' => (int) $info['acl_role_id'],
                        ]
                    )
                );
            }

            return $return;
        }

        /**
         * Insert multiple Acl Group Role links, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return boolean
         */
        public static function inserts(array $infos) {
            $return = parent::_inserts($infos);

            // Delete Cache
            foreach ($infos as $info) {
                // BY_ACL_GROUP
                if (array_key_exists('acl_group_id', $info)) {
                    parent::_cache_delete(
                        parent::_build_key(
                            self::BY_ACL_GROUP,
                            [
                                'acl_group_id' => (int) $info['acl_group_id'],
                            ]
                        )
                    );
                }

                // BY_ACL_GROUP_ACL_ROLE
                if (array_key_exists('acl_group_id', $info) && array_key_exists('acl_role_id', $info)) {
                    parent::_cache_delete(
                        parent::_build_key(
                            self::BY_ACL_GROUP_ACL_ROLE,
                            [
                                'acl_group_id' => (int) $info['acl_group_id'],
                                'acl_role_id'  => (int) $info['acl_role_id'],
                            ]
                        )
                    );
                }

                // BY_ACL_ROLE
                if (array_key_exists('acl_role_id', $info)) {
                    parent::_cache_delete(
                        parent::_build_key(
                            self::BY_ACL_ROLE,
                            [
                                'acl_role_id' => (int) $info['acl_role_id'],
                            ]
                        )
                    );
                }
            }

            return $return;
        }

        /**
         * Update Acl Group Role link records based on $where inputs
         *
         * @param array $new_info the new link record data
         * @param array $where associative array, matching columns with values
         *
         * @return bool
         */
        public static function update(array $new_info, array $where) {
            $return = parent::_update($new_info, $where);

            // Delete Cache
            // BY_ACL_GROUP
            if (array_key_exists('acl_group_id', $new_info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_ACL_GROUP,
                        [
                            'acl_group_id' => (int) $new_info['acl_group_id'],
                        ]
                    )
                );
            }
            if (array_key_exists('acl_group_id', $where)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_ACL_GROUP,
                        [
                            'acl_group_id' => (int) $where['acl_group_id'],
                        ]
                    )
                );
            }

            // BY_ACL_GROUP_ACL_ROLE
            if (array_key_exists('acl_group_id', $new_info) && array_key_exists('acl_role_id', $new_info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_ACL_GROUP_ACL_ROLE,
                        [
                            'acl_group_id' => (int) $new_info['acl_group_id'],
                            'acl_role_id'  => (int) $new_info['acl_role_id'],
                        ]
                    )
                );
            }
            if (array_key_exists('acl_group_id', $where) && array_key_exists('acl_role_id', $where)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_ACL_GROUP_ACL_ROLE,
                        [
                            'acl_group_id' => (int) $where['acl_group_id'],
                            'acl_role_id'  => (int) $where['acl_role_id'],
                        ]
                    )
                );
            }

            // BY_ACL_ROLE
            if (array_key_exists('acl_role_id', $new_info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_ACL_ROLE,
                        [
                            'acl_role_id' => (int) $new_info['acl_role_id'],
                        ]
                    )
                );
            }
            if (array_key_exists('acl_role_id', $where)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_ACL_ROLE,
                        [
                            'acl_role_id' => (int) $where['acl_role_id'],
                        ]
                    )
                );
            }

            return $return;
        }

        /**
         * Delete multiple Acl Group Role link records based on an array of associative arrays
         *
         * @param array $keys keys match the column names
         *
         * @return bool
         */
        public static function delete(array $keys) {
            $return = parent::_delete($keys);

            // Delete Cache
            // BY_ACL_GROUP
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_ACL_GROUP,
                    [
                        'acl_group_id' => (int) $keys['acl_group_id'],
                    ]
                )
            );

            // BY_ACL_GROUP_ACL_ROLE
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_ACL_GROUP_ACL_ROLE,
                    [
                        'acl_group_id' => (int) $keys['acl_group_id'],
                        'acl_role_id'  => (int) $keys['acl_role_id'],
                    ]
                )
            );

            // BY_ACL_ROLE
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_ACL_ROLE,
                    [
                        'acl_role_id' => (int) $keys['acl_role_id'],
                    ]
                )
            );

            return $return;
        }

        /**
         * Delete multiple sets of Acl Group Role link records based on an array of associative arrays
         *
         * @param array $keys_arr an array of arrays, keys match the column names
         *
         * @return bool
         */
        public static function deletes(array $keys_arr) {
            $return = parent::_deletes($keys_arr);

            // PRIMARY KEYS
            $unique_acl_group_id_arr = [];
            $unique_acl_role_id_arr = [];
            foreach ($keys_arr as $keys) {
                $unique_acl_group_id_arr[(int) $keys['acl_group_id']] = (int) $keys['acl_group_id'];
                $unique_acl_role_id_arr[(int) $keys['acl_role_id']] = (int) $keys['acl_role_id'];

                // BY_ACL_GROUP_ACL_ROLE
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_ACL_GROUP_ACL_ROLE,
                        [
                            'acl_group_id' => (int) $keys['acl_group_id'],
                            'acl_role_id'  => (int) $keys['acl_role_id'],
                        ]
                    )
                );
            }

            // BY_ACL_GROUP
            foreach ($unique_acl_group_id_arr as $acl_group_id) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_ACL_GROUP,
                        [
                            'acl_group_id' => (int) $acl_group_id,
                        ]
                    )
                );
            }

            // BY_ACL_ROLE
            foreach ($unique_acl_role_id_arr as $acl_role_id) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_ACL_ROLE,
                        [
                            'acl_role_id' => (int) $acl_role_id,
                        ]
                    )
                );
            }

            return $return;
        }
    }