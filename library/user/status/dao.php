<?php

    /**
     * User Status DAO
     */
    class user_status_dao extends record_dao implements user_status_definition {

        const BY_ALL  = 'by_all';
        const BY_NAME = 'by_name';

        public static function bindings() {
            return [
                'id'   => 'int',
                'name' => 'string',
            ];
        }

        // READS

        /**
         * Get User Status ids by name
         *
         * @param string $name
         *
         * @return array of User Status ids
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
         * Get all data for all User Status records
         *
         * @return array containing all User Status records
         */
        public static function all() {
            return parent::_all(self::BY_ALL);
        }

        // WRITES

        /**
         * Insert User Status record, created from an array of $info
         *
         * @param array $info associative array, keys matching columns in database for this entity
         *
         * @return user_status_model
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
         * Insert multiple User Status records, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return user_status_collection
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
         * Updates a User Status record with new data
         *   only fields that are specified in the $info array will be written
         *
         * @param user_status_model $user_status record to be updated
         * @param array $info data to write to the record
         *
         * @return user_status_model updated model
         */
        public static function update(user_status_model $user_status, array $info) {
            $updated_model = parent::_update($user_status, $info);

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
                            'name' => (string) $user_status->name,
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
         * Delete a User Status record
         *
         * @param user_status_model $user_status record to be deleted
         *
         * @return bool
         */
        public static function delete(user_status_model $user_status) {
            $return = parent::_delete($user_status);

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
                        'name' => (string) $user_status->name,
                    ]
                )
            );

            return $return;
        }

        /**
         * Delete multiple User Status records
         *
         * @param user_status_collection $user_status_collection records to be deleted
         *
         * @return bool
         */
        public static function deletes(user_status_collection $user_status_collection) {
            $return = parent::_deletes($user_status_collection);

            // Delete Cache
            // BY_ALL
            parent::_cache_delete(
                parent::_build_key(self::BY_ALL)
            );

            foreach ($user_status_collection as $user_status) {
                // BY_NAME
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_NAME,
                        [
                            'name' => (string) $user_status->name,
                        ]
                    )
                );

            }

            return $return;
        }
    }
