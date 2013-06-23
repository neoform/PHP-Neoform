<?php

    /**
     * User DAO
     */
    class user_dao extends record_dao implements user_definition {

        const BY_EMAIL               = 'by_email';
        const BY_PASSWORD_HASHMETHOD = 'by_password_hashmethod';
        const BY_STATUS              = 'by_status';

        /**
         * Get the generic bindings of the table columns
         *
         * @return array
         */
        public static function bindings() {
            return [
                'id'                  => 'int',
                'email'               => 'string',
                'password_hash'       => 'binary',
                'password_hashmethod' => 'int',
                'password_cost'       => 'int',
                'password_salt'       => 'binary',
                'status_id'           => 'int',
            ];
        }

        // READS

        /**
         * Get User ids by email
         *
         * @param string $email
         *
         * @return array of User ids
         */
        public static function by_email($email) {
            return self::_by_fields(
                self::BY_EMAIL,
                [
                    'email' => (string) $email,
                ]
            );
        }

        /**
         * Get User ids by password_hashmethod
         *
         * @param int $password_hashmethod
         *
         * @return array of User ids
         */
        public static function by_password_hashmethod($password_hashmethod) {
            return self::_by_fields(
                self::BY_PASSWORD_HASHMETHOD,
                [
                    'password_hashmethod' => (int) $password_hashmethod,
                ]
            );
        }

        /**
         * Get User ids by status
         *
         * @param int $status_id
         *
         * @return array of User ids
         */
        public static function by_status($status_id) {
            return self::_by_fields(
                self::BY_STATUS,
                [
                    'status_id' => (int) $status_id,
                ]
            );
        }

        /**
         * Get multiple sets of User ids by user_hashmethod
         *
         * @param user_hashmethod_collection|array $user_hashmethod_list
         *
         * @return array of arrays containing User ids
         */
        public static function by_password_hashmethod_multi($user_hashmethod_list) {
            $keys = [];
            if ($user_hashmethod_list instanceof user_hashmethod_collection) {
                foreach ($user_hashmethod_list as $k => $user_hashmethod) {
                    $keys[$k] = [
                        'password_hashmethod' => (int) $user_hashmethod->id,
                    ];
                }
            } else {
                foreach ($user_hashmethod_list as $k => $user_hashmethod) {
                    $keys[$k] = [
                        'password_hashmethod' => (int) $user_hashmethod,
                    ];
                }
            }
            return self::_by_fields_multi(self::BY_PASSWORD_HASHMETHOD, $keys);
        }

        /**
         * Get multiple sets of User ids by user_status
         *
         * @param user_status_collection|array $user_status_list
         *
         * @return array of arrays containing User ids
         */
        public static function by_status_multi($user_status_list) {
            $keys = [];
            if ($user_status_list instanceof user_status_collection) {
                foreach ($user_status_list as $k => $user_status) {
                    $keys[$k] = [
                        'status_id' => (int) $user_status->id,
                    ];
                }
            } else {
                foreach ($user_status_list as $k => $user_status) {
                    $keys[$k] = [
                        'status_id' => (int) $user_status,
                    ];
                }
            }
            return self::_by_fields_multi(self::BY_STATUS, $keys);
        }

        /**
         * Get User id_arr by an array of emails
         *
         * @param array $email_arr an array containing emails
         *
         * @return array of arrays of User ids
         */
        public static function by_email_multi(array $email_arr) {
            $keys_arr = [];
            foreach ($email_arr as $k => $email) {
                $keys_arr[$k] = [ 'email' => (string) $email, ];
            }
            return self::_by_fields_multi(
                self::BY_EMAIL,
                $keys_arr
            );
        }

        /**
         * Get a paginated list of user ids
         *
         * @param string  $order_by
         * @param string  $direction
         * @param integer $offset
         * @param integer $limit
         *
         * @return array
         */
        public static function pagination($order_by, $direction, $offset, $limit) {
            $users = core::sql('slave')->prepare("
                SELECT id
                FROM user
                ORDER BY {$order_by} {$direction}
                LIMIT {$limit}
                OFFSET {$offset}
            ");
            $users->execute();
            $ids = [];
            foreach ($users->fetchAll() as $user) {
                $ids[] = (int) $user['id'];
            }
            return $ids;
        }

        // WRITES

        /**
         * Insert User record, created from an array of $info
         *
         * @param array $info associative array, keys matching columns in database for this entity
         *
         * @return user_model
         */
        public static function insert(array $info) {

            // Insert record
            $return = parent::_insert($info);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

            // Delete Cache
            // BY_EMAIL
            if (array_key_exists('email', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_EMAIL,
                        [
                            'email' => (string) $info['email'],
                        ]
                    )
                );
            }

            // BY_PASSWORD_HASHMETHOD
            if (array_key_exists('password_hashmethod', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_PASSWORD_HASHMETHOD,
                        [
                            'password_hashmethod' => (int) $info['password_hashmethod'],
                        ]
                    )
                );
            }

            // BY_STATUS
            if (array_key_exists('status_id', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_STATUS,
                        [
                            'status_id' => (int) $info['status_id'],
                        ]
                    )
                );
            }

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

            return $return;
        }

        /**
         * Insert multiple User records, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return user_collection
         */
        public static function inserts(array $infos) {

            // Insert records
            $return = parent::_inserts($infos);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

            // Delete Cache
            foreach ($infos as $info) {
                // BY_EMAIL
                if (array_key_exists('email', $info)) {
                    parent::_cache_delete(
                        parent::_build_key(
                            self::BY_EMAIL,
                            [
                                'email' => (string) $info['email'],
                            ]
                        )
                    );
                }

                // BY_PASSWORD_HASHMETHOD
                if (array_key_exists('password_hashmethod', $info)) {
                    parent::_cache_delete(
                        parent::_build_key(
                            self::BY_PASSWORD_HASHMETHOD,
                            [
                                'password_hashmethod' => (int) $info['password_hashmethod'],
                            ]
                        )
                    );
                }

                // BY_STATUS
                if (array_key_exists('status_id', $info)) {
                    parent::_cache_delete(
                        parent::_build_key(
                            self::BY_STATUS,
                            [
                                'status_id' => (int) $info['status_id'],
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
         * Updates a User record with new data
         *   only fields that are specified in the $info array will be written
         *
         * @param user_model $user record to be updated
         * @param array $info data to write to the record
         *
         * @return user_model updated model
         */
        public static function update(user_model $user, array $info) {

            // Update record
            $updated_model = parent::_update($user, $info);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

            // Delete Cache
            // BY_EMAIL
            if (array_key_exists('email', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_EMAIL,
                        [
                            'email' => (string) $user->email,
                        ]
                    )
                );
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_EMAIL,
                        [
                            'email' => (string) $info['email'],
                        ]
                    )
                );
            }

            // BY_PASSWORD_HASHMETHOD
            if (array_key_exists('password_hashmethod', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_PASSWORD_HASHMETHOD,
                        [
                            'password_hashmethod' => (int) $user->password_hashmethod,
                        ]
                    )
                );
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_PASSWORD_HASHMETHOD,
                        [
                            'password_hashmethod' => (int) $info['password_hashmethod'],
                        ]
                    )
                );
            }

            // BY_STATUS
            if (array_key_exists('status_id', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_STATUS,
                        [
                            'status_id' => (int) $user->status_id,
                        ]
                    )
                );
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_STATUS,
                        [
                            'status_id' => (int) $info['status_id'],
                        ]
                    )
                );
            }

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

            return $updated_model;
        }

        /**
         * Delete a User record
         *
         * @param user_model $user record to be deleted
         *
         * @return bool
         */
        public static function delete(user_model $user) {

            // Delete record
            $return = parent::_delete($user);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

            // Delete Cache
            // BY_EMAIL
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_EMAIL,
                    [
                        'email' => (string) $user->email,
                    ]
                )
            );

            // BY_PASSWORD_HASHMETHOD
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_PASSWORD_HASHMETHOD,
                    [
                        'password_hashmethod' => (int) $user->password_hashmethod,
                    ]
                )
            );

            // BY_STATUS
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_STATUS,
                    [
                        'status_id' => (int) $user->status_id,
                    ]
                )
            );

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

            return $return;
        }

        /**
         * Delete multiple User records
         *
         * @param user_collection $user_collection records to be deleted
         *
         * @return bool
         */
        public static function deletes(user_collection $user_collection) {

            // Delete records
            $return = parent::_deletes($user_collection);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

            // Delete Cache
            foreach ($user_collection as $user) {
                // BY_EMAIL
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_EMAIL,
                        [
                            'email' => (string) $user->email,
                        ]
                    )
                );

                // BY_PASSWORD_HASHMETHOD
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_PASSWORD_HASHMETHOD,
                        [
                            'password_hashmethod' => (int) $user->password_hashmethod,
                        ]
                    )
                );

                // BY_STATUS
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_STATUS,
                        [
                            'status_id' => (int) $user->status_id,
                        ]
                    )
                );
            }

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

            return $return;
        }
    }
