<?php

    /**
     * User Site link DAO
     */
    class user_site_dao extends link_dao implements user_site_definition {

        const BY_SITE      = 'by_site';
        const BY_SITE_USER = 'by_site_user';
        const BY_USER      = 'by_user';

        /**
         * Get the generic bindings of the table columns
         *
         * @return array
         */
        public static function bindings() {
            return [
                'user_id' => 'int',
                'site_id' => 'int',
            ];
        }

        // READS

        /**
         * Get user_id by site_id
         *
         * @param int $site_id
         *
         * @return array result set containing user_id
         */
        public static function by_site($site_id) {
            return self::_by_fields(
                self::BY_SITE,
                [
                    'user_id',
                ],
                [
                    'site_id' => (int) $site_id,
                ]
            );
        }

        /**
         * Get user_id and site_id by site_id and user_id
         *
         * @param int $site_id
         * @param int $user_id
         *
         * @return array result set containing user_id and site_id
         */
        public static function by_site_user($site_id, $user_id) {
            return self::_by_fields(
                self::BY_SITE_USER,
                [
                    'user_id',
                    'site_id',
                ],
                [
                    'site_id' => (int) $site_id,
                    'user_id' => (int) $user_id,
                ]
            );
        }

        /**
         * Get site_id by user_id
         *
         * @param int $user_id
         *
         * @return array result set containing site_id
         */
        public static function by_user($user_id) {
            return self::_by_fields(
                self::BY_USER,
                [
                    'site_id',
                ],
                [
                    'user_id' => (int) $user_id,
                ]
            );
        }

        /**
         * Get multiple sets of site_id by user_id
         *
         * @param user_collection $user_collection
         *
         * @return array of result sets containing site_id
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
                    'site_id',
                ],
                $keys
            );
        }

        /**
         * Get multiple sets of user_id by site_id
         *
         * @param site_collection $site_collection
         *
         * @return array of result sets containing user_id
         */
        public static function by_site_multi(site_collection $site_collection) {
            $keys = [];
            foreach ($site_collection as $k => $site) {
                $keys[$k] = [
                    'site_id' => (int) $site->id,
                ];
            }

            return self::_by_fields_multi(
                self::BY_SITE,
                [
                    'user_id',
                ],
                $keys
            );
        }

        // WRITES

        /**
         * Insert User Site link, created from an array of $info
         *
         * @param array $info associative array, keys matching columns in database for this entity
         *
         * @return boolean
         */
        public static function insert(array $info) {

            // Insert link
            $return = parent::_insert($info);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

            // Delete Cache
            // BY_SITE
            if (array_key_exists('site_id', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_SITE,
                        [
                            'site_id' => (int) $info['site_id'],
                        ]
                    )
                );
            }

            // BY_SITE_USER
            if (array_key_exists('site_id', $info) && array_key_exists('user_id', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_SITE_USER,
                        [
                            'site_id' => (int) $info['site_id'],
                            'user_id' => (int) $info['user_id'],
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
         * Insert multiple User Site links, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return boolean
         */
        public static function inserts(array $infos) {

            // Insert links
            $return = parent::_inserts($infos);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

            // Delete Cache
            foreach ($infos as $info) {
                // BY_SITE
                if (array_key_exists('site_id', $info)) {
                    parent::_cache_delete(
                        parent::_build_key(
                            self::BY_SITE,
                            [
                                'site_id' => (int) $info['site_id'],
                            ]
                        )
                    );
                }

                // BY_SITE_USER
                if (array_key_exists('site_id', $info) && array_key_exists('user_id', $info)) {
                    parent::_cache_delete(
                        parent::_build_key(
                            self::BY_SITE_USER,
                            [
                                'site_id' => (int) $info['site_id'],
                                'user_id' => (int) $info['user_id'],
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
         * Update User Site link records based on $where inputs
         *
         * @param array $new_info the new link record data
         * @param array $where associative array, matching columns with values
         *
         * @return bool
         */
        public static function update(array $new_info, array $where) {

            // Update link
            $return = parent::_update($new_info, $where);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

            // Delete Cache
            // BY_SITE
            if (array_key_exists('site_id', $new_info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_SITE,
                        [
                            'site_id' => (int) $new_info['site_id'],
                        ]
                    )
                );
            }
            if (array_key_exists('site_id', $where)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_SITE,
                        [
                            'site_id' => (int) $where['site_id'],
                        ]
                    )
                );
            }

            // BY_SITE_USER
            if (array_key_exists('site_id', $new_info) && array_key_exists('user_id', $new_info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_SITE_USER,
                        [
                            'site_id' => (int) $new_info['site_id'],
                            'user_id' => (int) $new_info['user_id'],
                        ]
                    )
                );
            }
            if (array_key_exists('site_id', $where) && array_key_exists('user_id', $where)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_SITE_USER,
                        [
                            'site_id' => (int) $where['site_id'],
                            'user_id' => (int) $where['user_id'],
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
         * Delete multiple User Site link records based on an array of associative arrays
         *
         * @param array $keys keys match the column names
         *
         * @return bool
         */
        public static function delete(array $keys) {

            // Delete link
            $return = parent::_delete($keys);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

            // Delete Cache
            // BY_SITE
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_SITE,
                    [
                        'site_id' => (int) $keys['site_id'],
                    ]
                )
            );

            // BY_SITE_USER
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_SITE_USER,
                    [
                        'site_id' => (int) $keys['site_id'],
                        'user_id' => (int) $keys['user_id'],
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
         * Delete multiple sets of User Site link records based on an array of associative arrays
         *
         * @param array $keys_arr an array of arrays, keys match the column names
         *
         * @return bool
         */
        public static function deletes(array $keys_arr) {

            // Delete links
            $return = parent::_deletes($keys_arr);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

            // PRIMARY KEYS
            $unique_site_id_arr = [];
            $unique_user_id_arr = [];
            foreach ($keys_arr as $keys) {
                $unique_site_id_arr[(int) $keys['site_id']] = (int) $keys['site_id'];
                $unique_user_id_arr[(int) $keys['user_id']] = (int) $keys['user_id'];

                // BY_SITE_USER
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_SITE_USER,
                        [
                            'site_id' => (int) $keys['site_id'],
                            'user_id' => (int) $keys['user_id'],
                        ]
                    )
                );
            }

            // BY_SITE
            foreach ($unique_site_id_arr as $site_id) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_SITE,
                        [
                            'site_id' => (int) $site_id,
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
