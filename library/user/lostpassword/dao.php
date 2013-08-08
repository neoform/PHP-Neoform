<?php

    /**
     * User Lostpassword DAO
     */
    class user_lostpassword_dao extends entity_record_dao implements user_lostpassword_definition {

        const BY_USER = 'by_user';

        /**
         * @var array $pdo_bindings list of fields and their corresponding PDO bindings
         */
        protected $pdo_bindings = [
            'hash'      => PDO::PARAM_STR,
            'user_id'   => PDO::PARAM_INT,
            'posted_on' => PDO::PARAM_STR,
        ];

        // READS

        /**
         * Get User Lostpassword hashs by user
         *
         * @param int $user_id
         *
         * @return array of User Lostpassword hashs
         */
        public function by_user($user_id) {
            return parent::_by_fields(
                self::BY_USER,
                [
                    'user_id' => (int) $user_id,
                ]
            );
        }

        /**
         * Get multiple sets of User Lostpassword hashs by user
         *
         * @param user_collection|array $user_list
         *
         * @return array of arrays containing User Lostpassword hashs
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
            return parent::_by_fields_multi(self::BY_USER, $keys);
        }

        // WRITES

        /**
         * Insert User Lostpassword record, created from an array of $info
         *
         * @param array $info associative array, keys matching columns in database for this entity
         *
         * @return user_lostpassword_model
         */
        public function insert(array $info) {

            // Insert record
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

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

            return $return;
        }

        /**
         * Insert multiple User Lostpassword records, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return user_lostpassword_collection
         */
        public function inserts(array $infos) {

            // Insert records
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
            }

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

            return $return;
        }

        /**
         * Updates a User Lostpassword record with new data
         *   only fields that are specified in the $info array will be written
         *
         * @param user_lostpassword_model $user_lostpassword record to be updated
         * @param array $info data to write to the record
         *
         * @return user_lostpassword_model updated model
         */
        public function update(user_lostpassword_model $user_lostpassword, array $info) {

            // Update record
            $updated_model = parent::_update($user_lostpassword, $info);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

            // Delete Cache
            // BY_USER
            if (array_key_exists('user_id', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_USER,
                        [
                            'user_id' => (int) $user_lostpassword->user_id,
                        ]
                    )
                );
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

            return $updated_model;
        }

        /**
         * Delete a User Lostpassword record
         *
         * @param user_lostpassword_model $user_lostpassword record to be deleted
         *
         * @return bool
         */
        public function delete(user_lostpassword_model $user_lostpassword) {

            // Delete record
            $return = parent::_delete($user_lostpassword);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

            // Delete Cache
            // BY_USER
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_USER,
                    [
                        'user_id' => (int) $user_lostpassword->user_id,
                    ]
                )
            );

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

            return $return;
        }

        /**
         * Delete multiple User Lostpassword records
         *
         * @param user_lostpassword_collection $user_lostpassword_collection records to be deleted
         *
         * @return bool
         */
        public function deletes(user_lostpassword_collection $user_lostpassword_collection) {

            // Delete records
            $return = parent::_deletes($user_lostpassword_collection);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

            // Delete Cache
            foreach ($user_lostpassword_collection as $user_lostpassword) {
                // BY_USER
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_USER,
                        [
                            'user_id' => (int) $user_lostpassword->user_id,
                        ]
                    )
                );
            }

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

            return $return;
        }
    }
