<?php

    /**
     * Acl Role DAO
     */
    class acl_role_dao extends record_dao implements acl_role_definition {

        const BY_NAME = 'by_name';

        /**
         * Get the generic bindings of the table columns
         *
         * @return array
         */
        public static function bindings() {
            return [
                'id'   => 'int',
                'name' => 'string',
            ];
        }


        // READS

        /**
         * Get Acl Role ids by name
         *
         * @param string $name
         *
         * @return array of Acl Role ids
         */
        public static function by_name($name) {
            return self::_by_fields(
                self::BY_NAME,
                [
                    'name' => (string) $name,
                ]
            );
        }

        // WRITES

        /**
         * Insert Acl Role record, created from an array of $info
         *
         * @param array $info associative array, keys matching columns in database for this entity
         *
         * @return acl_role_model
         */
        public static function insert(array $info) {
            $return = parent::_insert($info);

            // Delete Cache
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
         * Insert multiple Acl Role records, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return acl_role_collection
         */
        public static function inserts(array $infos) {
            $return = parent::_inserts($infos);

            // Delete Cache
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
         * Updates a Acl Role record with new data
         *   only fields that are specified in the $info array will be written
         *
         * @param acl_role_model $acl_role record to be updated
         * @param array $info data to write to the record
         *
         * @return acl_role_model updated model
         */
        public static function update(acl_role_model $acl_role, array $info) {
            $updated_model = parent::_update($acl_role, $info);

            // Delete Cache
            // BY_NAME
            if (array_key_exists('name', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_NAME,
                        [
                            'name' => (string) $acl_role->name,
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
         * Delete a Acl Role record
         *
         * @param acl_role_model $acl_role record to be deleted
         *
         * @return bool
         */
        public static function delete(acl_role_model $acl_role) {
            $return = parent::_delete($acl_role);

            // Delete Cache
            // BY_NAME
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_NAME,
                    [
                        'name' => (string) $acl_role->name,
                    ]
                )
            );

            return $return;
        }

        /**
         * Delete multiple Acl Role records
         *
         * @param acl_role_collection $acl_role_collection records to be deleted
         *
         * @return bool
         */
        public static function deletes(acl_role_collection $acl_role_collection) {
            $return = parent::_deletes($acl_role_collection);

            // Delete Cache
            foreach ($acl_role_collection as $acl_role) {
                // BY_NAME
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_NAME,
                        [
                            'name' => (string) $acl_role->name,
                        ]
                    )
                );
            }

            return $return;
        }
    }
