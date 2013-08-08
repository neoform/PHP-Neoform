<?php

    /**
     * City DAO
     */
    class city_dao extends entity_record_dao implements city_definition {

        const BY_REGION                 = 'by_region';
        const BY_REGION_NAME_NORMALIZED = 'by_region_name_normalized';
        const BY_NAME_NORMALIZED        = 'by_name_normalized';
        const BY_NAME_SOUNDEX           = 'by_name_soundex';

        /**
         * Get the generic bindings of the table columns
         *
         * @return array
         */
        public static function bindings() {
            return [
                'id'              => PDO::PARAM_INT,
                'region_id'       => PDO::PARAM_INT,
                'name'            => PDO::PARAM_STR,
                'name_normalized' => PDO::PARAM_STR,
                'name_soundex'    => PDO::PARAM_STR,
                'top'             => PDO::PARAM_STR,
                'longitude'       => PDO::PARAM_STR,
                'latitude'        => PDO::PARAM_STR,
            ];
        }

        // READS

        /**
         * Get City ids by region
         *
         * @param int $region_id
         *
         * @return array of City ids
         */
        public function by_region($region_id) {
            return parent::_by_fields(
                self::BY_REGION,
                [
                    'region_id'       => (int) $region_id,
                ]
            );
        }

        /**
         * Get City ids by region and name_normalized
         *
         * @param int $region_id
         * @param string $name_normalized
         *
         * @return array of City ids
         */
        public function by_region_name_normalized($region_id, $name_normalized) {
            return parent::_by_fields(
                self::BY_REGION_NAME_NORMALIZED,
                [
                    'region_id'       => (int) $region_id,
                    'name_normalized' => (string) $name_normalized,
                ]
            );
        }

        /**
         * Get City ids by name_normalized
         *
         * @param string $name_normalized
         *
         * @return array of City ids
         */
        public function by_name_normalized($name_normalized) {
            return parent::_by_fields(
                self::BY_NAME_NORMALIZED,
                [
                    'name_normalized' => (string) $name_normalized,
                ]
            );
        }

        /**
         * Get City ids by name_soundex
         *
         * @param string $name_soundex
         *
         * @return array of City ids
         */
        public function by_name_soundex($name_soundex) {
            return parent::_by_fields(
                self::BY_NAME_SOUNDEX,
                [
                    'name_soundex' => (string) $name_soundex,
                ]
            );
        }

        /**
         * Get multiple sets of City ids by region
         *
         * @param region_collection|array $region_list
         *
         * @return array of arrays containing City ids
         */
        public function by_region_multi($region_list) {
            $keys = [];
            if ($region_list instanceof region_collection) {
                foreach ($region_list as $k => $region) {
                    $keys[$k] = [
                        'region_id' => (int) $region->id,
                    ];
                }
            } else {
                foreach ($region_list as $k => $region) {
                    $keys[$k] = [
                        'region_id' => (int) $region,
                    ];
                }
            }
            return parent::_by_fields_multi(self::BY_REGION, $keys);
        }

        /**
         * Get City id_arr by an array of region and name_normalizeds
         *
         * @param array $region_name_normalized_arr an array of arrays containing region_ids and name_normalizeds
         *
         * @return array of arrays of City ids
         */
        public function by_region_name_normalized_multi(array $region_name_normalized_arr) {
            $keys_arr = [];
            foreach ($region_name_normalized_arr as $k => $region_name_normalized) {
                $keys_arr[$k] = [
                    'region_id'       => (int) $region_name_normalized['region_id'],
                    'name_normalized' => (string) $region_name_normalized['name_normalized'],
                ];
            }
            return parent::_by_fields_multi(
                self::BY_REGION_NAME_NORMALIZED,
                $keys_arr
            );
        }

        /**
         * Get City id_arr by an array of name_normalizeds
         *
         * @param array $name_normalized_arr an array containing name_normalizeds
         *
         * @return array of arrays of City ids
         */
        public function by_name_normalized_multi(array $name_normalized_arr) {
            $keys_arr = [];
            foreach ($name_normalized_arr as $k => $name_normalized) {
                $keys_arr[$k] = [ 'name_normalized' => (string) $name_normalized, ];
            }
            return parent::_by_fields_multi(
                self::BY_NAME_NORMALIZED,
                $keys_arr
            );
        }

        /**
         * Get City id_arr by an array of name_soundexs
         *
         * @param array $name_soundex_arr an array containing name_soundexs
         *
         * @return array of arrays of City ids
         */
        public function by_name_soundex_multi(array $name_soundex_arr) {
            $keys_arr = [];
            foreach ($name_soundex_arr as $k => $name_soundex) {
                $keys_arr[$k] = [ 'name_soundex' => (string) $name_soundex, ];
            }
            return parent::_by_fields_multi(
                self::BY_NAME_SOUNDEX,
                $keys_arr
            );
        }

        // WRITES

        /**
         * Insert City record, created from an array of $info
         *
         * @param array $info associative array, keys matching columns in database for this entity
         *
         * @return city_model
         */
        public function insert(array $info) {

            // Insert record
            $return = parent::_insert($info);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

            // Delete Cache
            // BY_REGION
            if (array_key_exists('region_id', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_REGION,
                        [
                            'region_id' => (int) $info['region_id'],
                        ]
                    )
                );
            }

            // BY_REGION_NAME_NORMALIZED
            if (array_key_exists('region_id', $info) && array_key_exists('name_normalized', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_REGION_NAME_NORMALIZED,
                        [
                            'region_id'       => (int) $info['region_id'],
                            'name_normalized' => (string) $info['name_normalized'],
                        ]
                    )
                );
            }

            // BY_NAME_NORMALIZED
            if (array_key_exists('name_normalized', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_NAME_NORMALIZED,
                        [
                            'name_normalized' => (string) $info['name_normalized'],
                        ]
                    )
                );
            }

            // BY_NAME_SOUNDEX
            if (array_key_exists('name_soundex', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_NAME_SOUNDEX,
                        [
                            'name_soundex' => (string) $info['name_soundex'],
                        ]
                    )
                );
            }

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

            return $return;
        }

        /**
         * Insert multiple City records, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return city_collection
         */
        public function inserts(array $infos) {

            // Insert records
            $return = parent::_inserts($infos);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

            // Delete Cache
            foreach ($infos as $info) {
                // BY_REGION
                if (array_key_exists('region_id', $info)) {
                    parent::_cache_delete(
                        parent::_build_key(
                            self::BY_REGION,
                            [
                                'region_id' => (int) $info['region_id'],
                            ]
                        )
                    );
                }

                // BY_REGION_NAME_NORMALIZED
                if (array_key_exists('region_id', $info) && array_key_exists('name_normalized', $info)) {
                    parent::_cache_delete(
                        parent::_build_key(
                            self::BY_REGION_NAME_NORMALIZED,
                            [
                                'region_id'       => (int) $info['region_id'],
                                'name_normalized' => (string) $info['name_normalized'],
                            ]
                        )
                    );
                }

                // BY_NAME_NORMALIZED
                if (array_key_exists('name_normalized', $info)) {
                    parent::_cache_delete(
                        parent::_build_key(
                            self::BY_NAME_NORMALIZED,
                            [
                                'name_normalized' => (string) $info['name_normalized'],
                            ]
                        )
                    );
                }

                // BY_NAME_SOUNDEX
                if (array_key_exists('name_soundex', $info)) {
                    parent::_cache_delete(
                        parent::_build_key(
                            self::BY_NAME_SOUNDEX,
                            [
                                'name_soundex' => (string) $info['name_soundex'],
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
         * Updates a City record with new data
         *   only fields that are specified in the $info array will be written
         *
         * @param city_model $city record to be updated
         * @param array $info data to write to the record
         *
         * @return city_model updated model
         */
        public function update(city_model $city, array $info) {

            // Update record
            $updated_model = parent::_update($city, $info);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

            // Delete Cache
            // BY_REGION
            if (array_key_exists('region_id', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_REGION,
                        [
                            'region_id'       => (int) $city->region_id,
                        ]
                    )
                );
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_REGION,
                        [
                            'region_id'       => (int) $info['region_id'],
                        ]
                    )
                );
            }

            // BY_REGION_NAME_NORMALIZED
            if (array_key_exists('region_id', $info) && array_key_exists('name_normalized', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_REGION_NAME_NORMALIZED,
                        [
                            'region_id'       => (int) $city->region_id,
                            'name_normalized' => (string) $city->name_normalized,
                        ]
                    )
                );
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_REGION_NAME_NORMALIZED,
                        [
                            'region_id'       => (int) $info['region_id'],
                            'name_normalized' => (string) $info['name_normalized'],
                        ]
                    )
                );
            }

            // BY_NAME_NORMALIZED
            if (array_key_exists('name_normalized', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_NAME_NORMALIZED,
                        [
                            'name_normalized' => (string) $city->name_normalized,
                        ]
                    )
                );
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_NAME_NORMALIZED,
                        [
                            'name_normalized' => (string) $info['name_normalized'],
                        ]
                    )
                );
            }

            // BY_NAME_SOUNDEX
            if (array_key_exists('name_soundex', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_NAME_SOUNDEX,
                        [
                            'name_soundex' => (string) $city->name_soundex,
                        ]
                    )
                );
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_NAME_SOUNDEX,
                        [
                            'name_soundex' => (string) $info['name_soundex'],
                        ]
                    )
                );
            }

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

            return $updated_model;
        }

        /**
         * Delete a City record
         *
         * @param city_model $city record to be deleted
         *
         * @return bool
         */
        public function delete(city_model $city) {

            // Delete record
            $return = parent::_delete($city);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

            // Delete Cache
            // BY_REGION
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_REGION,
                    [
                        'region_id' => (int) $city->region_id,
                    ]
                )
            );

            // BY_REGION_NAME_NORMALIZED
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_REGION_NAME_NORMALIZED,
                    [
                        'region_id'       => (int) $city->region_id,
                        'name_normalized' => (string) $city->name_normalized,
                    ]
                )
            );

            // BY_NAME_NORMALIZED
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_NAME_NORMALIZED,
                    [
                        'name_normalized' => (string) $city->name_normalized,
                    ]
                )
            );

            // BY_NAME_SOUNDEX
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_NAME_SOUNDEX,
                    [
                        'name_soundex' => (string) $city->name_soundex,
                    ]
                )
            );

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

            return $return;
        }

        /**
         * Delete multiple City records
         *
         * @param city_collection $city_collection records to be deleted
         *
         * @return bool
         */
        public function deletes(city_collection $city_collection) {

            // Delete records
            $return = parent::_deletes($city_collection);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

            // Delete Cache
            foreach ($city_collection as $city) {
                // BY_REGION
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_REGION,
                        [
                            'region_id'       => (int) $city->region_id,
                        ]
                    )
                );

                // BY_REGION_NAME_NORMALIZED
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_REGION_NAME_NORMALIZED,
                        [
                            'region_id'       => (int) $city->region_id,
                            'name_normalized' => (string) $city->name_normalized,
                        ]
                    )
                );

                // BY_NAME_NORMALIZED
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_NAME_NORMALIZED,
                        [
                            'name_normalized' => (string) $city->name_normalized,
                        ]
                    )
                );

                // BY_NAME_SOUNDEX
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_NAME_SOUNDEX,
                        [
                            'name_soundex' => (string) $city->name_soundex,
                        ]
                    )
                );
            }

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

            return $return;
        }
    }
