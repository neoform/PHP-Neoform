<?php

    /**
     * User Acl Role link DAO
     */
    class user_acl_role_dao extends entity_link_dao implements user_acl_role_definition {

        const BY_USER          = 'by_user';
        const BY_USER_ACL_ROLE = 'by_user_acl_role';
        const BY_ACL_ROLE      = 'by_acl_role';

        /**
         * @var array $pdo_bindings list of fields and their corresponding PDO bindings
         */
        protected $pdo_bindings = [
            'user_id'     => PDO::PARAM_INT,
            'acl_role_id' => PDO::PARAM_INT,
        ];

        // READS

        /**
         * Get acl_role_id by user_id
         *
         * @param int $user_id
         *
         * @return array result set containing acl_role_id
         */
        public function by_user($user_id) {
            return parent::_by_fields(
                self::BY_USER,
                [
                    'acl_role_id',
                ],
                [
                    'user_id' => (int) $user_id,
                ]
            );
        }

        /**
         * Get user_id and acl_role_id by user_id and acl_role_id
         *
         * @param int $user_id
         * @param int $acl_role_id
         *
         * @return array result set containing user_id and acl_role_id
         */
        public function by_user_acl_role($user_id, $acl_role_id) {
            return parent::_by_fields(
                self::BY_USER_ACL_ROLE,
                [
                    'user_id',
                    'acl_role_id',
                ],
                [
                    'user_id'     => (int) $user_id,
                    'acl_role_id' => (int) $acl_role_id,
                ]
            );
        }

        /**
         * Get user_id by acl_role_id
         *
         * @param int $acl_role_id
         *
         * @return array result set containing user_id
         */
        public function by_acl_role($acl_role_id) {
            return parent::_by_fields(
                self::BY_ACL_ROLE,
                [
                    'user_id',
                ],
                [
                    'acl_role_id' => (int) $acl_role_id,
                ]
            );
        }

        /**
         * Get multiple sets of acl_role_id by a collection of users
         *
         * @param user_collection|array $user_list
         *
         * @return array of result sets containing acl_role_id
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
                    'acl_role_id',
                ],
                $keys
            );
        }

        /**
         * Get multiple sets of user_id by a collection of acl_roles
         *
         * @param acl_role_collection|array $acl_role_list
         *
         * @return array of result sets containing user_id
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
                    'user_id',
                ],
                $keys
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

            // Insert link
            $return = parent::_insert($info);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

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

            // BY_USER_ACL_ROLE
            if (array_key_exists('user_id', $info) && array_key_exists('acl_role_id', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_USER_ACL_ROLE,
                        [
                            'user_id'     => (int) $info['user_id'],
                            'acl_role_id' => (int) $info['acl_role_id'],
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
         * Insert multiple User Acl Role links, created from an array of arrays of $info
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

                // BY_USER_ACL_ROLE
                if (array_key_exists('user_id', $info) && array_key_exists('acl_role_id', $info)) {
                    parent::_cache_delete(
                        parent::_build_key(
                            self::BY_USER_ACL_ROLE,
                            [
                                'user_id'     => (int) $info['user_id'],
                                'acl_role_id' => (int) $info['acl_role_id'],
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
         * Update User Acl Role link records based on $where inputs
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

            // BY_USER_ACL_ROLE
            if (array_key_exists('user_id', $new_info) && array_key_exists('acl_role_id', $new_info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_USER_ACL_ROLE,
                        [
                            'user_id'     => (int) $new_info['user_id'],
                            'acl_role_id' => (int) $new_info['acl_role_id'],
                        ]
                    )
                );
            }
            if (array_key_exists('user_id', $where) && array_key_exists('acl_role_id', $where)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_USER_ACL_ROLE,
                        [
                            'user_id'     => (int) $where['user_id'],
                            'acl_role_id' => (int) $where['acl_role_id'],
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
         * Delete multiple User Acl Role link records based on an array of associative arrays
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
            // BY_USER
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_USER,
                    [
                        'user_id' => (int) $keys['user_id'],
                    ]
                )
            );

            // BY_USER_ACL_ROLE
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_USER_ACL_ROLE,
                    [
                        'user_id'     => (int) $keys['user_id'],
                        'acl_role_id' => (int) $keys['acl_role_id'],
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
         * Delete multiple sets of User Acl Role link records based on an array of associative arrays
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
            $unique_user_id_arr = [];
            $unique_acl_role_id_arr = [];
            foreach ($keys_arr as $keys) {
                $unique_user_id_arr[(int) $keys['user_id']] = (int) $keys['user_id'];
                $unique_acl_role_id_arr[(int) $keys['acl_role_id']] = (int) $keys['acl_role_id'];

                // BY_USER_ACL_ROLE
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_USER_ACL_ROLE,
                        [
                            'user_id'     => (int) $keys['user_id'],
                            'acl_role_id' => (int) $keys['acl_role_id'],
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
