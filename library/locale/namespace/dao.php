<?php

    /**
     * Locale Namespace DAO
     */
    class locale_namespace_dao extends entity_record_dao implements locale_namespace_definition {

        const BY_ALL  = 'by_all';
        const BY_NAME = 'by_name';

        /**
         * @var array $pdo_bindings list of fields and their corresponding PDO bindings
         */
        protected $pdo_bindings = [
            'id'   => PDO::PARAM_INT,
            'name' => PDO::PARAM_STR,
        ];

        // READS

        /**
         * Get Locale Namespace ids by name
         *
         * @param string $name
         *
         * @return array of Locale Namespace ids
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
         * Get Locale Namespace id_arr by an array of names
         *
         * @param array $name_arr an array containing names
         *
         * @return array of arrays of Locale Namespace ids
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
         * Get all data for all Locale Namespace records
         *
         * @return array containing all Locale Namespace records
         */
        public function all() {
            return parent::_all(self::BY_ALL);
        }

        // WRITES

        /**
         * Insert Locale Namespace record, created from an array of $info
         *
         * @param array $info associative array, keys matching columns in database for this entity
         *
         * @return locale_namespace_model
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
         * Insert multiple Locale Namespace records, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return locale_namespace_collection
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
         * Updates a Locale Namespace record with new data
         *   only fields that are specified in the $info array will be written
         *
         * @param locale_namespace_model $locale_namespace record to be updated
         * @param array $info data to write to the record
         *
         * @return locale_namespace_model updated model
         */
        public function update(locale_namespace_model $locale_namespace, array $info) {

            // Update record
            $updated_model = parent::_update($locale_namespace, $info);

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
                            'name' => (string) $locale_namespace->name,
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
         * Delete a Locale Namespace record
         *
         * @param locale_namespace_model $locale_namespace record to be deleted
         *
         * @return bool
         */
        public function delete(locale_namespace_model $locale_namespace) {

            // Delete record
            $return = parent::_delete($locale_namespace);

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
                        'name' => (string) $locale_namespace->name,
                    ]
                )
            );

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

            return $return;
        }

        /**
         * Delete multiple Locale Namespace records
         *
         * @param locale_namespace_collection $locale_namespace_collection records to be deleted
         *
         * @return bool
         */
        public function deletes(locale_namespace_collection $locale_namespace_collection) {

            // Delete records
            $return = parent::_deletes($locale_namespace_collection);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

            // Delete Cache
            // BY_ALL
            parent::_cache_delete(
                parent::_build_key(self::BY_ALL)
            );

            foreach ($locale_namespace_collection as $locale_namespace) {
                // BY_NAME
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_NAME,
                        [
                            'name' => (string) $locale_namespace->name,
                        ]
                    )
                );
            }

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

            return $return;
        }
    }
