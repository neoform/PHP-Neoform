<?php

    /**
     * Acl Group DAO
     */
    class acl_group_dao extends record_dao implements acl_group_definition {

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
         * Get Acl Group ids by name
         *
         * @param string $name
         *
         * @return array of Acl Group ids
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
         * Insert Acl Group record, created from an array of $info
         *
         * @param array $info associative array, keys matching columns in database for this entity
         *
         * @return acl_group_model
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
         * Insert multiple Acl Group records, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return acl_group_collection
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
         * Updates a Acl Group record with new data
         *   only fields that are specified in the $info array will be written
         *
         * @param acl_group_model $acl_group record to be updated
         * @param array $info data to write to the record
         *
         * @return acl_group_model updated model
         */
        public static function update(acl_group_model $acl_group, array $info) {
            $updated_model = parent::_update($acl_group, $info);

            // Delete Cache
            // BY_NAME
            if (array_key_exists('name', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_NAME,
                        [
                            'name' => (string) $acl_group->name,
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
         * Delete a Acl Group record
         *
         * @param acl_group_model $acl_group record to be deleted
         *
         * @return bool
         */
        public static function delete(acl_group_model $acl_group) {
            $return = parent::_delete($acl_group);

            // Delete Cache
            // BY_NAME
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_NAME,
                    [
                        'name' => (string) $acl_group->name,
                    ]
                )
            );

            return $return;
        }

        /**
         * Delete multiple Acl Group records
         *
         * @param acl_group_collection $acl_group_collection records to be deleted
         *
         * @return bool
         */
        public static function deletes(acl_group_collection $acl_group_collection) {
            $return = parent::_deletes($acl_group_collection);

            // Delete Cache
            foreach ($acl_group_collection as $acl_group) {
                // BY_NAME
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_NAME,
                        [
                            'name' => (string) $acl_group->name,
                        ]
                    )
                );
            }

            return $return;
        }
    }
