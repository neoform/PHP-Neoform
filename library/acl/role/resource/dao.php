<?php

    /**
     * Acl Role Resource link DAO
     */
    class acl_role_resource_dao extends link_dao implements acl_role_resource_definition {

        const BY_ACL_ROLE              = 'by_acl_role';
        const BY_ACL_ROLE_ACL_RESOURCE = 'by_acl_role_acl_resource';
        const BY_ACL_RESOURCE          = 'by_acl_resource';

        /**
         * Get the generic bindings of the table columns
         *
         * @return array
         */
        public static function bindings() {
            return [
                'acl_role_id'     => 'int',
                'acl_resource_id' => 'int',
            ];
        }


        // READS

        /**
         * Get acl_resource_id by acl_role_id
         *
         * @param int $acl_role_id
         *
         * @return array result set containing acl_resource_id
         */
        public static function by_acl_role($acl_role_id) {
            return self::_by_fields(
                self::BY_ACL_ROLE,
                [
                    'acl_resource_id',
                ],
                [
                    'acl_role_id' => (int) $acl_role_id,
                ]
            );
        }

        /**
         * Get acl_role_id and acl_resource_id by acl_role_id and acl_resource_id
         *
         * @param int $acl_role_id
         * @param int $acl_resource_id
         *
         * @return array result set containing acl_role_id and acl_resource_id
         */
        public static function by_acl_role_acl_resource($acl_role_id, $acl_resource_id) {
            return self::_by_fields(
                self::BY_ACL_ROLE_ACL_RESOURCE,
                [
                    'acl_role_id',
                    'acl_resource_id',
                ],
                [
                    'acl_role_id'     => (int) $acl_role_id,
                    'acl_resource_id' => (int) $acl_resource_id,
                ]
            );
        }

        /**
         * Get acl_role_id by acl_resource_id
         *
         * @param int $acl_resource_id
         *
         * @return array result set containing acl_role_id
         */
        public static function by_acl_resource($acl_resource_id) {
            return self::_by_fields(
                self::BY_ACL_RESOURCE,
                [
                    'acl_role_id',
                ],
                [
                    'acl_resource_id' => (int) $acl_resource_id,
                ]
            );
        }

        /**
         * Get multiple sets of acl_resource_id by acl_role_id
         *
         * @param acl_role_collection $acl_role_collection
         *
         * @return array of result sets containing acl_resource_id
         */
        public static function by_acl_role_multi(acl_role_collection $acl_role_collection) {
            $keys = [];
            foreach ($acl_role_collection as $k => $acl_role) {
                $keys[$k] = [
                    'acl_role_id' => (int) $acl_role->id,
                ];
            }

            return self::_by_fields_multi(
                self::BY_ACL_ROLE,
                [
                    'acl_resource_id',
                ],
                $keys
            );
        }

        /**
         * Get multiple sets of acl_role_id by acl_resource_id
         *
         * @param acl_resource_collection $acl_resource_collection
         *
         * @return array of result sets containing acl_role_id
         */
        public static function by_acl_resource_multi(acl_resource_collection $acl_resource_collection) {
            $keys = [];
            foreach ($acl_resource_collection as $k => $acl_resource) {
                $keys[$k] = [
                    'acl_resource_id' => (int) $acl_resource->id,
                ];
            }

            return self::_by_fields_multi(
                self::BY_ACL_RESOURCE,
                [
                    'acl_role_id',
                ],
                $keys
            );
        }

        // WRITES

        /**
         * Insert Acl Role Resource link, created from an array of $info
         *
         * @param array $info associative array, keys matching columns in database for this entity
         *
         * @return boolean
         */
        public static function insert(array $info) {
            $return = parent::_insert($info);

            // Delete Cache
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

            // BY_ACL_ROLE_ACL_RESOURCE
            if (array_key_exists('acl_role_id', $info) && array_key_exists('acl_resource_id', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_ACL_ROLE_ACL_RESOURCE,
                        [
                            'acl_role_id'     => (int) $info['acl_role_id'],
                            'acl_resource_id' => (int) $info['acl_resource_id'],
                        ]
                    )
                );
            }

            // BY_ACL_RESOURCE
            if (array_key_exists('acl_resource_id', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_ACL_RESOURCE,
                        [
                            'acl_resource_id' => (int) $info['acl_resource_id'],
                        ]
                    )
                );
            }

            return $return;
        }

        /**
         * Insert multiple Acl Role Resource links, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return boolean
         */
        public static function inserts(array $infos) {
            $return = parent::_inserts($infos);

            // Delete Cache
            foreach ($infos as $info) {
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

                // BY_ACL_ROLE_ACL_RESOURCE
                if (array_key_exists('acl_role_id', $info) && array_key_exists('acl_resource_id', $info)) {
                    parent::_cache_delete(
                        parent::_build_key(
                            self::BY_ACL_ROLE_ACL_RESOURCE,
                            [
                                'acl_role_id'     => (int) $info['acl_role_id'],
                                'acl_resource_id' => (int) $info['acl_resource_id'],
                            ]
                        )
                    );
                }

                // BY_ACL_RESOURCE
                if (array_key_exists('acl_resource_id', $info)) {
                    parent::_cache_delete(
                        parent::_build_key(
                            self::BY_ACL_RESOURCE,
                            [
                                'acl_resource_id' => (int) $info['acl_resource_id'],
                            ]
                        )
                    );
                }
            }

            return $return;
        }

        /**
         * Update Acl Role Resource link records based on $where inputs
         *
         * @param array $new_info the new link record data
         * @param array $where associative array, matching columns with values
         *
         * @return bool
         */
        public static function update(array $new_info, array $where) {
            $return = parent::_update($new_info, $where);

            // Delete Cache
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

            // BY_ACL_ROLE_ACL_RESOURCE
            if (array_key_exists('acl_role_id', $new_info) && array_key_exists('acl_resource_id', $new_info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_ACL_ROLE_ACL_RESOURCE,
                        [
                            'acl_role_id'     => (int) $new_info['acl_role_id'],
                            'acl_resource_id' => (int) $new_info['acl_resource_id'],
                        ]
                    )
                );
            }
            if (array_key_exists('acl_role_id', $where) && array_key_exists('acl_resource_id', $where)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_ACL_ROLE_ACL_RESOURCE,
                        [
                            'acl_role_id'     => (int) $where['acl_role_id'],
                            'acl_resource_id' => (int) $where['acl_resource_id'],
                        ]
                    )
                );
            }

            // BY_ACL_RESOURCE
            if (array_key_exists('acl_resource_id', $new_info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_ACL_RESOURCE,
                        [
                            'acl_resource_id' => (int) $new_info['acl_resource_id'],
                        ]
                    )
                );
            }
            if (array_key_exists('acl_resource_id', $where)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_ACL_RESOURCE,
                        [
                            'acl_resource_id' => (int) $where['acl_resource_id'],
                        ]
                    )
                );
            }

            return $return;
        }

        /**
         * Delete multiple Acl Role Resource link records based on an array of associative arrays
         *
         * @param array $keys keys match the column names
         *
         * @return bool
         */
        public static function delete(array $keys) {
            $return = parent::_delete($keys);

            // Delete Cache
            // BY_ACL_ROLE
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_ACL_ROLE,
                    [
                        'acl_role_id' => (int) $keys['acl_role_id'],
                    ]
                )
            );

            // BY_ACL_ROLE_ACL_RESOURCE
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_ACL_ROLE_ACL_RESOURCE,
                    [
                        'acl_role_id'     => (int) $keys['acl_role_id'],
                        'acl_resource_id' => (int) $keys['acl_resource_id'],
                    ]
                )
            );

            // BY_ACL_RESOURCE
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_ACL_RESOURCE,
                    [
                        'acl_resource_id' => (int) $keys['acl_resource_id'],
                    ]
                )
            );

            return $return;
        }

        /**
         * Delete multiple sets of Acl Role Resource link records based on an array of associative arrays
         *
         * @param array $keys_arr an array of arrays, keys match the column names
         *
         * @return bool
         */
        public static function deletes(array $keys_arr) {
            $return = parent::_deletes($keys_arr);

            // PRIMARY KEYS
            $unique_acl_role_id_arr = [];
            $unique_acl_resource_id_arr = [];
            foreach ($keys_arr as $keys) {
                $unique_acl_role_id_arr[(int) $keys['acl_role_id']] = (int) $keys['acl_role_id'];
                $unique_acl_resource_id_arr[(int) $keys['acl_resource_id']] = (int) $keys['acl_resource_id'];

                // BY_ACL_ROLE_ACL_RESOURCE
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_ACL_ROLE_ACL_RESOURCE,
                        [
                            'acl_role_id'     => (int) $keys['acl_role_id'],
                            'acl_resource_id' => (int) $keys['acl_resource_id'],
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

            // BY_ACL_RESOURCE
            foreach ($unique_acl_resource_id_arr as $acl_resource_id) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_ACL_RESOURCE,
                        [
                            'acl_resource_id' => (int) $acl_resource_id,
                        ]
                    )
                );
            }

            return $return;
        }
    }
