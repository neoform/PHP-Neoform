<?php

    /**
     * User Lostpassword DAO
     */
    class user_lostpassword_dao extends record_dao implements user_lostpassword_definition {

        const BY_USER = 'by_user';

        public static function castings() {
            return [
                'hash'      => 'binary',
                'user_id'   => 'int',
                'posted_on' => 'string',
            ];
        }

        // READS

        /**
         * Get User Lostpassword hashs by user
         *
         * @param int $user_id
         *
         * @return array of User Lostpassword hashs
         */
        public static function by_user($user_id) {
            return self::_by_fields(
                self::BY_USER,
                [
                    'user_id' => (int) $user_id,
                ]
            );
        }

        /**
         * Get multiple sets of User Lostpassword hashs by user
         *
         * @param user_collection $user_collection
         *
         * @return array of arrays containing User Lostpassword hashs
         */
        public static function by_user_multi(user_collection $user_collection) {
            $keys = [];
            foreach ($user_collection as $k => $user) {
                $keys[$k] = [
                    'user_id' => (int) $user->id,
                ];
            }
            return self::_by_fields_multi(self::BY_USER, $keys);
        }

        // WRITES

        /**
         * Insert User Lostpassword record, created from an array of $info
         *
         * @param array $info associative array, keys matching columns in database for this entity
         *
         * @return user_lostpassword_model
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

            return $return;
        }

        /**
         * Insert multiple User Lostpassword records, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return user_lostpassword_collection
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

            }

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
        public static function update(user_lostpassword_model $user_lostpassword, array $info) {
            $updated_model = parent::_update($user_lostpassword, $info);

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

            return $updated_model;
        }

        /**
         * Delete a User Lostpassword record
         *
         * @param user_lostpassword_model $user_lostpassword record to be deleted
         *
         * @return bool
         */
        public static function delete(user_lostpassword_model $user_lostpassword) {
            $return = parent::_delete($user_lostpassword);

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

            return $return;
        }

        /**
         * Delete multiple User Lostpassword records
         *
         * @param user_lostpassword_collection $user_lostpassword_collection records to be deleted
         *
         * @return bool
         */
        public static function deletes(user_lostpassword_collection $user_lostpassword_collection) {
            $return = parent::_deletes($user_lostpassword_collection);

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

            return $return;
        }
    }
