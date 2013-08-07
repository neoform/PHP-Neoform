<?php

    /**
     * Auth DAO
     */
    class auth_dao extends record_dao implements auth_definition {

        const BY_USER = 'by_user';

        protected $pdo_bindings = [
            'hash'       => 'binary',
            'user_id'    => 'int',
            'expires_on' => 'string',
        ];

        // READS

        /**
         * Get Auth hashs by user
         *
         * @param int $user_id
         *
         * @return array of Auth hashs
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
         * Get multiple sets of Auth hashs by user
         *
         * @param user_collection $user_collection
         *
         * @return array of arrays containing Auth hashs
         */
        public function by_user_multi(user_collection $user_collection) {
            $keys = [];
            foreach ($user_collection as $k => $user) {
                $keys[$k] = [
                    'user_id' => (int) $user->id,
                ];
            }
            return parent::_by_fields_multi(self::BY_USER, $keys);
        }

        // WRITES

        /**
         * Insert Auth record, created from an array of $info
         *
         * @param array $info associative array, keys matching columns in database for this entity
         *
         * @return auth_model
         */
        public function insert(array $info) {

            // Insert record
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

            return $return;
        }

        /**
         * Insert multiple Auth records, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return auth_collection
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
         * Updates a Auth record with new data
         *   only fields that are specified in the $info array will be written
         *
         * @param auth_model $auth record to be updated
         * @param array $info data to write to the record
         *
         * @return auth_model updated model
         */
        public function update(auth_model $auth, array $info) {

            // Update record
            $updated_model = parent::_update($auth, $info);

            // Delete Cache
            // BY_USER
            if (array_key_exists('user_id', $info)) {

                // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
                parent::cache_batch_start();

                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_USER,
                        [
                            'user_id' => (int) $auth->user_id,
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

                // Execute pipelined cache deletion queries (if supported by cache engine)
                parent::cache_batch_execute();
            }

            return $updated_model;
        }

        /**
         * Delete a Auth record
         *
         * @param auth_model $auth record to be deleted
         *
         * @return bool
         */
        public function delete(auth_model $auth) {

            // Delete record
            $return = parent::_delete($auth);

            // Delete Cache
            // BY_USER
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_USER,
                    [
                        'user_id' => (int) $auth->user_id,
                    ]
                )
            );

            return $return;
        }

        /**
         * Delete multiple Auth records
         *
         * @param auth_collection $auth_collection records to be deleted
         *
         * @return bool
         */
        public function deletes(auth_collection $auth_collection) {

            // Delete records
            $return = parent::_deletes($auth_collection);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

            // Delete Cache
            foreach ($auth_collection as $auth) {
                // BY_USER
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_USER,
                        [
                            'user_id' => (int) $auth->user_id,
                        ]
                    )
                );
            }

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

            return $return;
        }
    }
