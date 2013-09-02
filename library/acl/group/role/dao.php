<?php

    /**
     * Acl Group Role link DAO
     */
    class acl_group_role_dao extends entity_link_dao implements acl_group_role_definition {

        const BY_ACL_GROUP          = 'by_acl_group';
        const BY_ACL_GROUP_ACL_ROLE = 'by_acl_group_acl_role';
        const BY_ACL_ROLE           = 'by_acl_role';

        /**
         * $var array $field_bindings list of fields and their corresponding bindings
         *
         * @return array
         */
        protected $field_bindings = [
            'acl_group_id' => self::TYPE_INTEGER,
            'acl_role_id'  => self::TYPE_INTEGER,
        ];

        // READS

        /**
         * Get acl_role_id by acl_group_id
         *
         * @param int $acl_group_id
         *
         * @return array result set containing acl_role_id
         */
        public function by_acl_group($acl_group_id) {
            return parent::_by_fields(
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
        public function by_acl_group_acl_role($acl_group_id, $acl_role_id) {
            return parent::_by_fields(
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
        public function by_acl_role($acl_role_id) {
            return parent::_by_fields(
                self::BY_ACL_ROLE,
                [
                    'acl_group_id',
                ],
                [
                    'acl_role_id' => (int) $acl_role_id,
                ]
            );
        }

        /**
         * Get multiple sets of acl_role_id by a collection of acl_groups
         *
         * @param acl_group_collection|array $acl_group_list
         *
         * @return array of result sets containing acl_role_id
         */
        public function by_acl_group_multi($acl_group_list) {
            $keys = [];
            if ($acl_group_list instanceof acl_group_collection) {
                foreach ($acl_group_list as $k => $acl_group) {
                    $keys[$k] = [
                        'acl_group_id' => (int) $acl_group->id,
                    ];
                }

            } else {
                foreach ($acl_group_list as $k => $acl_group) {
                    $keys[$k] = [
                        'acl_group_id' => (int) $acl_group,
                    ];
                }

            }

            return parent::_by_fields_multi(
                self::BY_ACL_GROUP,
                [
                    'acl_role_id',
                ],
                $keys
            );
        }

        /**
         * Get multiple sets of acl_group_id by a collection of acl_roles
         *
         * @param acl_role_collection|array $acl_role_list
         *
         * @return array of result sets containing acl_group_id
         */
        public function by_acl_role_multi($acl_role_list) {
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
                    'acl_group_id',
                ],
                $keys
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
        public function insert(array $info) {

            // Insert link
            $return = parent::_insert($info);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

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

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

            return $return;
        }

        /**
         * Insert multiple Acl Group Role links, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return boolean
         */
        public function insert_multi(array $infos) {

            // Insert links
            $return = parent::_insert_multi($infos);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

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

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

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
        public function update(array $new_info, array $where) {

            // Update link
            $return = parent::_update($new_info, $where);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

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

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

            return $return;
        }

        /**
         * Delete multiple Acl Group Role link records based on an array of associative arrays
         *
         * @param array $keys keys match the column names
         *
         * @return bool
         */
        public function delete(array $keys) {

            // Delete link
            $return = parent::_delete($keys);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

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

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

            return $return;
        }

        /**
         * Delete multiple sets of Acl Group Role link records based on an array of associative arrays
         *
         * @param array $keys_arr an array of arrays, keys match the column names
         *
         * @return bool
         */
        public function delete_multi(array $keys_arr) {

            // Delete links
            $return = parent::_delete_multi($keys_arr);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

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

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

            return $return;
        }
    }
