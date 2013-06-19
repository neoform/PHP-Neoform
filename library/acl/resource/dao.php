<?php

    /**
     * Acl Resource DAO
     */
    class acl_resource_dao extends record_dao implements acl_resource_definition {

        const BY_NAME   = 'by_name';
        const BY_PARENT = 'by_parent';

        /**
         * Get the generic bindings of the table columns
         *
         * @return array
         */
        public static function bindings() {
            return [
                'id'        => 'int',
                'parent_id' => 'int',
                'name'      => 'string',
            ];
        }

        // READS

        /**
         * Get Acl Resource ids by name
         *
         * @param string $name
         *
         * @return array of Acl Resource ids
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
         * Get Acl Resource ids by an array of names
         *
         * @param array $names
         *
         * @return array of arrays of Acl Resource ids
         */
        public static function by_name_multi(array $names) {
            $keys_arr = [];
            foreach ($names as $k => $name) {
                $keys_arr[$k] = [ 'name' => (string) $name, ];
            }

            return self::_by_fields_multi(
                self::BY_NAME,
                $keys_arr
            );
        }

        /**
         * Get Acl Resource ids by parent
         *
         * @param int $parent_id
         *
         * @return array of Acl Resource ids
         */
        public static function by_parent($parent_id) {
            return self::_by_fields(
                self::BY_PARENT,
                [
                    'parent_id' => $parent_id === null ? null : (int) $parent_id,
                ]
            );
        }

        // WRITES

        /**
         * Insert Acl Resource record, created from an array of $info
         *
         * @param array $info associative array, keys matching columns in database for this entity
         *
         * @return acl_resource_model
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

            // BY_PARENT
            if (array_key_exists('parent_id', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_PARENT,
                        [
                            'parent_id' => $info['parent_id'] === null ? null : (int) $info['parent_id'],
                        ]
                    )
                );
            }

            return $return;
        }

        /**
         * Insert multiple Acl Resource records, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return acl_resource_collection
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

                // BY_PARENT
                if (array_key_exists('parent_id', $info)) {
                    parent::_cache_delete(
                        parent::_build_key(
                            self::BY_PARENT,
                            [
                                'parent_id' => $info['parent_id'] === null ? null : (int) $info['parent_id'],
                            ]
                        )
                    );
                }
            }

            return $return;
        }

        /**
         * Updates a Acl Resource record with new data
         *   only fields that are specified in the $info array will be written
         *
         * @param acl_resource_model $acl_resource record to be updated
         * @param array $info data to write to the record
         *
         * @return acl_resource_model updated model
         */
        public static function update(acl_resource_model $acl_resource, array $info) {
            $updated_model = parent::_update($acl_resource, $info);

            // Delete Cache
            // BY_NAME
            if (array_key_exists('name', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_NAME,
                        [
                            'name' => (string) $acl_resource->name,
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

            // BY_PARENT
            if (array_key_exists('parent_id', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_PARENT,
                        [
                            'parent_id' => $acl_resource->parent_id === null ? null : (int) $acl_resource->parent_id,
                        ]
                    )
                );
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_PARENT,
                        [
                            'parent_id' => $info['parent_id'] === null ? null : (int) $info['parent_id'],
                        ]
                    )
                );
            }

            return $updated_model;
        }

        /**
         * Delete a Acl Resource record
         *
         * @param acl_resource_model $acl_resource record to be deleted
         *
         * @return bool
         */
        public static function delete(acl_resource_model $acl_resource) {
            $return = parent::_delete($acl_resource);

            // Delete Cache
            // BY_NAME
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_NAME,
                    [
                        'name' => (string) $acl_resource->name,
                    ]
                )
            );

            // BY_PARENT
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_PARENT,
                    [
                        'parent_id' => $acl_resource->parent_id === null ? null : (int) $acl_resource->parent_id,
                    ]
                )
            );

            return $return;
        }

        /**
         * Delete multiple Acl Resource records
         *
         * @param acl_resource_collection $acl_resource_collection records to be deleted
         *
         * @return bool
         */
        public static function deletes(acl_resource_collection $acl_resource_collection) {
            $return = parent::_deletes($acl_resource_collection);

            // Delete Cache
            foreach ($acl_resource_collection as $acl_resource) {
                // BY_NAME
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_NAME,
                        [
                            'name' => (string) $acl_resource->name,
                        ]
                    )
                );

                // BY_PARENT
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_PARENT,
                        [
                            'parent_id' => $acl_resource->parent_id === null ? null : (int) $acl_resource->parent_id,
                        ]
                    )
                );
            }

            return $return;
        }
    }
