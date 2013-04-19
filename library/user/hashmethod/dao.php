<?php

    /**
     * User Hashmethod DAO
     */
    class user_hashmethod_dao extends record_dao implements user_hashmethod_definition {

        const BY_ALL  = 'by_all';
        const BY_NAME = 'by_name';

        public static function castings() {
            return [
                'id'   => 'int',
                'name' => 'string',
            ];
        }

        // READS

        /**
         * Get User Hashmethod ids by name
         *
         * @param string $name
         *
         * @return array of User Hashmethod ids
         */
        public static function by_name($name) {
            return self::_by_fields(
                self::BY_NAME,
                [
                    'name' => (string) $name,
                ]
            );
        }

        /**
         * Get all data for all User Hashmethod records
         *
         * @return array containing all User Hashmethod records
         */
        public static function all() {
            return parent::_all(self::BY_ALL);
        }

        // WRITES

        /**
         * Insert User Hashmethod record, created from an array of $info
         *
         * @param array $info associative array, keys matching columns in database for this entity
         *
         * @return user_hashmethod_model
         */
        public static function insert(array $info) {
            $return = parent::_insert($info);

            // Delete Cache
            // BY_ALL
            parent::_cache_delete(
                parent::_build_key(self::BY_ALL)
            );

            // BY_NAME
            if (array_key_exists('name', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_NAME,
                        [
                            'name' => (string) $info['name'],
                        ]
                    )
                );
            }

            return $return;
        }

        /**
         * Insert multiple User Hashmethod records, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return user_hashmethod_collection
         */
        public static function inserts(array $infos) {
            $return = parent::_inserts($infos);

            // Delete Cache
            // BY_ALL
            parent::_cache_delete(
                parent::_build_key(self::BY_ALL)
            );

            foreach ($infos as $info) {
                // BY_NAME
                if (array_key_exists('name', $info)) {
                    parent::_cache_delete(
                        parent::_build_key(
                            self::BY_NAME,
                            [
                                'name' => (string) $info['name'],
                            ]
                        )
                    );
                }

            }

            return $return;
        }

        /**
         * Updates a User Hashmethod record with new data
         *   only fields that are specified in the $info array will be written
         *
         * @param user_hashmethod_model $user_hashmethod record to be updated
         * @param array $info data to write to the record
         *
         * @return user_hashmethod_model updated model
         */
        public static function update(user_hashmethod_model $user_hashmethod, array $info) {
            $updated_model = parent::_update($user_hashmethod, $info);

            // Delete Cache
            // BY_ALL
            parent::_cache_delete(
                parent::_build_key(self::BY_ALL)
            );

            // BY_NAME
            if (array_key_exists('name', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_NAME,
                        [
                            'name' => (string) $user_hashmethod->name,
                        ]
                    )
                );
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_NAME,
                        [
                            'name' => (string) $info['name'],
                        ]
                    )
                );
            }

            return $updated_model;
        }

        /**
         * Delete a User Hashmethod record
         *
         * @param user_hashmethod_model $user_hashmethod record to be deleted
         *
         * @return bool
         */
        public static function delete(user_hashmethod_model $user_hashmethod) {
            $return = parent::_delete($user_hashmethod);

            // Delete Cache
            // BY_ALL
            parent::_cache_delete(
                parent::_build_key(self::BY_ALL)
            );

            // BY_NAME
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_NAME,
                    [
                        'name' => (string) $user_hashmethod->name,
                    ]
                )
            );

            return $return;
        }

        /**
         * Delete multiple User Hashmethod records
         *
         * @param user_hashmethod_collection $user_hashmethod_collection records to be deleted
         *
         * @return bool
         */
        public static function deletes(user_hashmethod_collection $user_hashmethod_collection) {
            $return = parent::_deletes($user_hashmethod_collection);

            // Delete Cache
            // BY_ALL
            parent::_cache_delete(
                parent::_build_key(self::BY_ALL)
            );

            foreach ($user_hashmethod_collection as $user_hashmethod) {
                // BY_NAME
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_NAME,
                        [
                            'name' => (string) $user_hashmethod->name,
                        ]
                    )
                );

            }

            return $return;
        }
    }
