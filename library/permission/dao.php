<?php

    /**
     * Permission DAO
     */
    class permission_dao extends record_dao implements permission_definition {

        const BY_ALL  = 'by_all';
        const BY_NAME = 'by_name';

        // READS

        /**
         * Get Permission ids by name
         *
         * @param string $name
         *
         * @return array of Permission ids
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
         * Get all data for all Permission records
         *
         * @return array containing all Permission records
         */
        public static function all() {
            return parent::_all(self::BY_ALL);
        }

        // WRITES

        /**
         * Insert Permission record, created from an array of $info
         *
         * @param array $info associative array, keys matching columns in database for this entity
         *
         * @return permission_model
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
         * Insert multiple Permission records, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return permission_collection
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
         * Updates a Permission record with new data
         *   only fields that are specified in the $info array will be written
         *
         * @param permission_model $permission record to be updated
         * @param array $info data to write to the record
         *
         * @return permission_model updated model
         */
        public static function update(permission_model $permission, array $info) {
            $updated_model = parent::_update($permission, $info);

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
                            'name' => (string) $permission->name,
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
         * Delete a Permission record
         *
         * @param permission_model $permission record to be deleted
         *
         * @return bool
         */
        public static function delete(permission_model $permission) {
            $return = parent::_delete($permission);

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
                        'name' => (string) $permission->name,
                    ]
                )
            );

            return $return;
        }

        /**
         * Delete multiple Permission records
         *
         * @param permission_collection $permission_collection records to be deleted
         *
         * @return bool
         */
        public static function deletes(permission_collection $permission_collection) {
            $return = parent::_deletes($permission_collection);

            // Delete Cache
            // BY_ALL
            parent::_cache_delete(
                parent::_build_key(self::BY_ALL)
            );

            foreach ($permission_collection as $permission) {
                // BY_NAME
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_NAME,
                        [
                            'name' => (string) $permission->name,
                        ]
                    )
                );

            }

            return $return;
        }

    }
