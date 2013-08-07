<?php

    /**
     * Acl Group DAO
     */
    class acl_group_dao extends entity_record_dao implements acl_group_definition {

        const BY_ALL  = 'by_all';
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
        public function by_name($name) {
            return self::_by_fields(
                self::BY_NAME,
                [
                    'name' => (string) $name,
                ]
            );
        }

        /**
         * Get Acl Group id_arr by an array of names
         *
         * @param array $name_arr an array containing names
         *
         * @return array of arrays of Acl Group ids
         */
        public function by_name_multi(array $name_arr) {
            $keys_arr = [];
            foreach ($name_arr as $k => $name) {
                $keys_arr[$k] = [ 'name' => (string) $name, ];
            }
            return self::_by_fields_multi(
                self::BY_NAME,
                $keys_arr
            );
        }

        /**
         * Get all data for all Acl Group records
         *
         * @return array containing all Acl Group records
         */
        public static function all() {
            return parent::_all(self::BY_ALL);
        }

        /**
         * Get a paginated list of group ids
         *
         * @param string  $order_by
         * @param string  $direction
         * @param integer $offset
         * @param integer $limit
         *
         * @return array
         */
        public static function pagination($order_by, $direction, $offset, $limit) {
            $users = core::sql(core::config()['sql']['default_pool_read'])->prepare("
                SELECT id
                FROM " . sql_lib::quote_field_name('acl_group') . "
                ORDER BY " . sql_lib::quote_field_name($order_by) . " {$direction}
                LIMIT {$limit}
                OFFSET {$offset}
            ");
            $users->execute();
            $ids = [];
            foreach ($users->fetchAll() as $user) {
                $ids[] = (int) $user['id'];
            }
            return $ids;
        }

        // WRITES

        /**
         * Insert Acl Group record, created from an array of $info
         *
         * @param array $info associative array, keys matching columns in database for this entity
         *
         * @return acl_group_model
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

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

            return $return;
        }

        /**
         * Insert multiple Acl Group records, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return acl_group_collection
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
            }

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

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
        public function update(acl_group_model $acl_group, array $info) {

            // Update record
            $updated_model = parent::_update($acl_group, $info);

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

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

            return $updated_model;
        }

        /**
         * Delete a Acl Group record
         *
         * @param acl_group_model $acl_group record to be deleted
         *
         * @return bool
         */
        public function delete(acl_group_model $acl_group) {

            // Delete record
            $return = parent::_delete($acl_group);

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
                        'name' => (string) $acl_group->name,
                    ]
                )
            );

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

            return $return;
        }

        /**
         * Delete multiple Acl Group records
         *
         * @param acl_group_collection $acl_group_collection records to be deleted
         *
         * @return bool
         */
        public function deletes(acl_group_collection $acl_group_collection) {

            // Delete records
            $return = parent::_deletes($acl_group_collection);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

            // Delete Cache
            // BY_ALL
            parent::_cache_delete(
                parent::_build_key(self::BY_ALL)
            );

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

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

            return $return;
        }
    }
