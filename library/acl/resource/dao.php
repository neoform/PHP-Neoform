<?php

    /**
     * Acl Resource DAO
     */
    class acl_resource_dao extends entity_record_dao implements acl_resource_definition {

        const BY_ALL    = 'by_all';
        const BY_NAME   = 'by_name';
        const BY_PARENT = 'by_parent';

        /**
         * @var array $pdo_bindings list of fields and their corresponding PDO bindings
         */
        protected $pdo_bindings = [
            'id'        => PDO::PARAM_INT,
            'parent_id' => PDO::PARAM_INT,
            'name'      => PDO::PARAM_STR,
        ];

        // READS

        /**
         * Get Acl Resource ids by name
         *
         * @param string $name
         *
         * @return array of Acl Resource ids
         */
        public function by_name($name) {
            return parent::_by_fields(
                self::BY_NAME,
                [
                    'name' => (string) $name,
                ]
            );
        }

        /**
         * Get Acl Resource ids by parent
         *
         * @param int $parent_id
         *
         * @return array of Acl Resource ids
         */
        public function by_parent($parent_id) {
            return parent::_by_fields(
                self::BY_PARENT,
                [
                    'parent_id' => $parent_id === null ? null : (int) $parent_id,
                ]
            );
        }

        /**
         * Get multiple sets of Acl Resource ids by acl_resource
         *
         * @param acl_resource_collection|array $acl_resource_list
         *
         * @return array of arrays containing Acl Resource ids
         */
        public function by_parent_multi($acl_resource_list) {
            $keys = [];
            if ($acl_resource_list instanceof acl_resource_collection) {
                foreach ($acl_resource_list as $k => $acl_resource) {
                    $keys[$k] = [
                        'parent_id' => $acl_resource->id === null ? null : (int) $acl_resource->id,
                    ];
                }
            } else {
                foreach ($acl_resource_list as $k => $acl_resource) {
                    $keys[$k] = [
                        'parent_id' => $acl_resource === null ? null : (int) $acl_resource,
                    ];
                }
            }
            return parent::_by_fields_multi(self::BY_PARENT, $keys);
        }

        /**
         * Get Acl Resource id_arr by an array of names
         *
         * @param array $name_arr an array containing names
         *
         * @return array of arrays of Acl Resource ids
         */
        public function by_name_multi(array $name_arr) {
            $keys_arr = [];
            foreach ($name_arr as $k => $name) {
                $keys_arr[$k] = [ 'name' => (string) $name, ];
            }
            return parent::_by_fields_multi(
                self::BY_NAME,
                $keys_arr
            );
        }

        /**
         * Get all data for all Acl Resource records
         *
         * @return array containing all Acl Resource records
         */
        public function all() {
            return parent::_all(self::BY_ALL);
        }

        // WRITES

        /**
         * Insert Acl Resource record, created from an array of $info
         *
         * @param array $info associative array, keys matching columns in database for this entity
         *
         * @return acl_resource_model
         */
        public function insert(array $info) {

            // Insert record
            $return = parent::_insert($info);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

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

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

            return $return;
        }

        /**
         * Insert multiple Acl Resource records, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return acl_resource_collection
         */
        public function inserts(array $infos) {

            // Insert records
            $return = parent::_inserts($infos);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

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

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

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
        public function update(acl_resource_model $acl_resource, array $info) {

            // Update record
            $updated_model = parent::_update($acl_resource, $info);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

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

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

            return $updated_model;
        }

        /**
         * Delete a Acl Resource record
         *
         * @param acl_resource_model $acl_resource record to be deleted
         *
         * @return bool
         */
        public function delete(acl_resource_model $acl_resource) {

            // Delete record
            $return = parent::_delete($acl_resource);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

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

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

            return $return;
        }

        /**
         * Delete multiple Acl Resource records
         *
         * @param acl_resource_collection $acl_resource_collection records to be deleted
         *
         * @return bool
         */
        public function deletes(acl_resource_collection $acl_resource_collection) {

            // Delete records
            $return = parent::_deletes($acl_resource_collection);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

            // Delete Cache
            // BY_ALL
            parent::_cache_delete(
                parent::_build_key(self::BY_ALL)
            );

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

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

            return $return;
        }
    }
