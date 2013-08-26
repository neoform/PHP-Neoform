<?php

    /**
     * Acl Group User link DAO
     */
    class acl_group_user_dao extends entity_link_dao implements acl_group_user_definition {

        const BY_ACL_GROUP      = 'by_acl_group';
        const BY_ACL_GROUP_USER = 'by_acl_group_user';
        const BY_USER           = 'by_user';

        /**
         * $var array $field_bindings list of fields and their corresponding bindings
         *
         * @return array
         */
        protected $field_bindings = [
            'acl_group_id' => self::TYPE_INTEGER,
            'user_id'      => self::TYPE_INTEGER,
        ];

        // READS

        /**
         * Get user_id by acl_group_id
         *
         * @param int $acl_group_id
         *
         * @return array result set containing user_id
         */
        public function by_acl_group($acl_group_id) {
            return parent::_by_fields(
                self::BY_ACL_GROUP,
                [
                    'user_id',
                ],
                [
                    'acl_group_id' => (int) $acl_group_id,
                ]
            );
        }

        /**
         * Get acl_group_id and user_id by acl_group_id and user_id
         *
         * @param int $acl_group_id
         * @param int $user_id
         *
         * @return array result set containing acl_group_id and user_id
         */
        public function by_acl_group_user($acl_group_id, $user_id) {
            return parent::_by_fields(
                self::BY_ACL_GROUP_USER,
                [
                    'acl_group_id',
                    'user_id',
                ],
                [
                    'acl_group_id' => (int) $acl_group_id,
                    'user_id'      => (int) $user_id,
                ]
            );
        }

        /**
         * Get acl_group_id by user_id
         *
         * @param int $user_id
         *
         * @return array result set containing acl_group_id
         */
        public function by_user($user_id) {
            return parent::_by_fields(
                self::BY_USER,
                [
                    'acl_group_id',
                ],
                [
                    'user_id' => (int) $user_id,
                ]
            );
        }

        /**
         * Get multiple sets of user_id by a collection of acl_groups
         *
         * @param acl_group_collection|array $acl_group_list
         *
         * @return array of result sets containing user_id
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
                    'user_id',
                ],
                $keys
            );
        }

        /**
         * Get multiple sets of acl_group_id by a collection of users
         *
         * @param user_collection|array $user_list
         *
         * @return array of result sets containing acl_group_id
         */
        public function by_user_multi($user_list) {
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
                    'acl_group_id',
                ],
                $keys
            );
        }

        // WRITES

        /**
         * Insert Acl Group User link, created from an array of $info
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

            // BY_ACL_GROUP_USER
            if (array_key_exists('acl_group_id', $info) && array_key_exists('user_id', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_ACL_GROUP_USER,
                        [
                            'acl_group_id' => (int) $info['acl_group_id'],
                            'user_id'      => (int) $info['user_id'],
                        ]
                    )
                );
            }

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

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

            return $return;
        }

        /**
         * Insert multiple Acl Group User links, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return boolean
         */
        public function inserts(array $infos) {

            // Insert links
            $return = parent::_inserts($infos);

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

                // BY_ACL_GROUP_USER
                if (array_key_exists('acl_group_id', $info) && array_key_exists('user_id', $info)) {
                    parent::_cache_delete(
                        parent::_build_key(
                            self::BY_ACL_GROUP_USER,
                            [
                                'acl_group_id' => (int) $info['acl_group_id'],
                                'user_id'      => (int) $info['user_id'],
                            ]
                        )
                    );
                }

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
            }

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

            return $return;
        }

        /**
         * Update Acl Group User link records based on $where inputs
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

            // BY_ACL_GROUP_USER
            if (array_key_exists('acl_group_id', $new_info) && array_key_exists('user_id', $new_info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_ACL_GROUP_USER,
                        [
                            'acl_group_id' => (int) $new_info['acl_group_id'],
                            'user_id'      => (int) $new_info['user_id'],
                        ]
                    )
                );
            }
            if (array_key_exists('acl_group_id', $where) && array_key_exists('user_id', $where)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_ACL_GROUP_USER,
                        [
                            'acl_group_id' => (int) $where['acl_group_id'],
                            'user_id'      => (int) $where['user_id'],
                        ]
                    )
                );
            }

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

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

            return $return;
        }

        /**
         * Delete multiple Acl Group User link records based on an array of associative arrays
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

            // BY_ACL_GROUP_USER
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_ACL_GROUP_USER,
                    [
                        'acl_group_id' => (int) $keys['acl_group_id'],
                        'user_id'      => (int) $keys['user_id'],
                    ]
                )
            );

            // BY_USER
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_USER,
                    [
                        'user_id' => (int) $keys['user_id'],
                    ]
                )
            );

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

            return $return;
        }

        /**
         * Delete multiple sets of Acl Group User link records based on an array of associative arrays
         *
         * @param array $keys_arr an array of arrays, keys match the column names
         *
         * @return bool
         */
        public function deletes(array $keys_arr) {

            // Delete links
            $return = parent::_deletes($keys_arr);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

            // PRIMARY KEYS
            $unique_acl_group_id_arr = [];
            $unique_user_id_arr = [];
            foreach ($keys_arr as $keys) {
                $unique_acl_group_id_arr[(int) $keys['acl_group_id']] = (int) $keys['acl_group_id'];
                $unique_user_id_arr[(int) $keys['user_id']] = (int) $keys['user_id'];

                // BY_ACL_GROUP_USER
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_ACL_GROUP_USER,
                        [
                            'acl_group_id' => (int) $keys['acl_group_id'],
                            'user_id'      => (int) $keys['user_id'],
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

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

            return $return;
        }
    }
