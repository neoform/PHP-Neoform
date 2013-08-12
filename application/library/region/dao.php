<?php

    /**
     * Region DAO
     */
    class region_dao extends entity_record_limit_dao implements region_definition {

        const BY_COUNTRY                 = 'by_country';
        const BY_COUNTRY_NAME_NORMALIZED = 'by_country_name_normalized';
        const BY_ISO2                    = 'by_iso2';
        const BY_COUNTRY_NAME            = 'by_country_name';
        const BY_NAME_SOUNDEX            = 'by_name_soundex';

        /**
         * @var array $pdo_bindings list of fields and their corresponding PDO bindings
         */
        protected $pdo_bindings = [
            'id'              => PDO::PARAM_INT,
            'country_id'      => PDO::PARAM_INT,
            'name'            => PDO::PARAM_STR,
            'name_normalized' => PDO::PARAM_STR,
            'name_soundex'    => PDO::PARAM_STR,
            'iso2'            => PDO::PARAM_STR,
            'longitude'       => PDO::PARAM_STR,
            'latitude'        => PDO::PARAM_STR,
        ];

        // READS

        /**
         * Get Region ids by country
         *
         * @param int $country_id
         *
         * @return array of Region ids
         */
        public function by_country($country_id) {
            return parent::_by_fields(
                self::BY_COUNTRY,
                [
                    'country_id' => (int) $country_id,
                ]
            );
        }

        public function by_country_offset($country_id, array $order_by, $offset, $limit) {
            return parent::_by_fields_offset(
                self::BY_COUNTRY,
                [
                    'country_id' => (int) $country_id,
                ],
                $order_by,
                (int) $offset,
                (int) $limit
            );
        }

        public function by_country_after($country_id, array $order_by, $after_pk, $limit) {
            return parent::_by_fields_after(
                self::BY_COUNTRY,
                [
                    'country_id' => (int) $country_id,
                ],
                $order_by,
                $after_pk,
                (int) $limit
            );
        }

        /**
         * Get Region ids by country and name_normalized
         *
         * @param int $country_id
         * @param string $name_normalized
         *
         * @return array of Region ids
         */
        public function by_country_name_normalized($country_id, $name_normalized) {
            return parent::_by_fields(
                self::BY_COUNTRY_NAME_NORMALIZED,
                [
                    'country_id'      => (int) $country_id,
                    'name_normalized' => (string) $name_normalized,
                ]
            );
        }

        /**
         * Get Region ids by iso2
         *
         * @param string $iso2
         *
         * @return array of Region ids
         */
        public function by_iso2($iso2) {
            return parent::_by_fields(
                self::BY_ISO2,
                [
                    'iso2' => (string) $iso2,
                ]
            );
        }

        /**
         * Get Region ids by country and name
         *
         * @param int $country_id
         * @param string $name
         *
         * @return array of Region ids
         */
        public function by_country_name($country_id, $name) {
            return parent::_by_fields(
                self::BY_COUNTRY_NAME,
                [
                    'country_id' => (int) $country_id,
                    'name'       => (string) $name,
                ]
            );
        }

        /**
         * Get Region ids by name_soundex
         *
         * @param string $name_soundex
         *
         * @return array of Region ids
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
         * Get multiple sets of Region ids by country
         *
         * @param country_collection|array $country_list
         *
         * @return array of arrays containing Region ids
         */
        public function by_country_multi($country_list) {
            $keys = [];
            if ($country_list instanceof country_collection) {
                foreach ($country_list as $k => $country) {
                    $keys[$k] = [
                        'country_id' => (int) $country->id,
                    ];
                }
            } else {
                foreach ($country_list as $k => $country) {
                    $keys[$k] = [
                        'country_id' => (int) $country,
                    ];
                }
            }
            return parent::_by_fields_multi(self::BY_COUNTRY, $keys);
        }

        /**
         * Get Region id_arr by an array of country and name_normalizeds
         *
         * @param array $country_name_normalized_arr an array of arrays containing country_ids and name_normalizeds
         *
         * @return array of arrays of Region ids
         */
        public function by_country_name_normalized_multi(array $country_name_normalized_arr) {
            $keys_arr = [];
            foreach ($country_name_normalized_arr as $k => $country_name_normalized) {
                $keys_arr[$k] = [
                    'country_id'      => (int) $country_name_normalized['country_id'],
                    'name_normalized' => (string) $country_name_normalized['name_normalized'],
                ];
            }
            return parent::_by_fields_multi(
                self::BY_COUNTRY_NAME_NORMALIZED,
                $keys_arr
            );
        }

        /**
         * Get Region id_arr by an array of iso2s
         *
         * @param array $iso2_arr an array containing iso2s
         *
         * @return array of arrays of Region ids
         */
        public function by_iso2_multi(array $iso2_arr) {
            $keys_arr = [];
            foreach ($iso2_arr as $k => $iso2) {
                $keys_arr[$k] = [ 'iso2' => (string) $iso2, ];
            }
            return parent::_by_fields_multi(
                self::BY_ISO2,
                $keys_arr
            );
        }

        /**
         * Get Region id_arr by an array of country and names
         *
         * @param array $country_name_arr an array of arrays containing country_ids and names
         *
         * @return array of arrays of Region ids
         */
        public function by_country_name_multi(array $country_name_arr) {
            $keys_arr = [];
            foreach ($country_name_arr as $k => $country_name) {
                $keys_arr[$k] = [
                    'country_id' => (int) $country_name['country_id'],
                    'name'       => (string) $country_name['name'],
                ];
            }
            return parent::_by_fields_multi(
                self::BY_COUNTRY_NAME,
                $keys_arr
            );
        }

        /**
         * Get Region id_arr by an array of name_soundexs
         *
         * @param array $name_soundex_arr an array containing name_soundexs
         *
         * @return array of arrays of Region ids
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
         * Insert Region record, created from an array of $info
         *
         * @param array $info associative array, keys matching columns in database for this entity
         *
         * @return region_model
         */
        public function insert(array $info) {

            // Insert record
            $return = parent::_insert($info);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

            // Delete Cache
            // BY_COUNTRY
            if (array_key_exists('country_id', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_COUNTRY,
                        [
                            'country_id' => (int) $info['country_id'],
                        ]
                    )
                );
            }

            // BY_COUNTRY_NAME_NORMALIZED
            if (array_key_exists('country_id', $info) && array_key_exists('name_normalized', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_COUNTRY_NAME_NORMALIZED,
                        [
                            'country_id'      => (int) $info['country_id'],
                            'name_normalized' => (string) $info['name_normalized'],
                        ]
                    )
                );
            }

            // BY_ISO2
            if (array_key_exists('iso2', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_ISO2,
                        [
                            'iso2' => (string) $info['iso2'],
                        ]
                    )
                );
            }

            // BY_COUNTRY_NAME
            if (array_key_exists('country_id', $info) && array_key_exists('name', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_COUNTRY_NAME,
                        [
                            'country_id' => (int) $info['country_id'],
                            'name'       => (string) $info['name'],
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
         * Insert multiple Region records, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return region_collection
         */
        public function inserts(array $infos) {

            // Insert records
            $return = parent::_inserts($infos);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

            // Delete Cache
            foreach ($infos as $info) {
                // BY_COUNTRY
                if (array_key_exists('country_id', $info)) {
                    parent::_cache_delete(
                        parent::_build_key(
                            self::BY_COUNTRY,
                            [
                                'country_id' => (int) $info['country_id'],
                            ]
                        )
                    );
                }

                // BY_COUNTRY_NAME_NORMALIZED
                if (array_key_exists('country_id', $info) && array_key_exists('name_normalized', $info)) {
                    parent::_cache_delete(
                        parent::_build_key(
                            self::BY_COUNTRY_NAME_NORMALIZED,
                            [
                                'country_id'      => (int) $info['country_id'],
                                'name_normalized' => (string) $info['name_normalized'],
                            ]
                        )
                    );
                }

                // BY_ISO2
                if (array_key_exists('iso2', $info)) {
                    parent::_cache_delete(
                        parent::_build_key(
                            self::BY_ISO2,
                            [
                                'iso2' => (string) $info['iso2'],
                            ]
                        )
                    );
                }

                // BY_COUNTRY_NAME
                if (array_key_exists('country_id', $info) && array_key_exists('name', $info)) {
                    parent::_cache_delete(
                        parent::_build_key(
                            self::BY_COUNTRY_NAME,
                            [
                                'country_id' => (int) $info['country_id'],
                                'name'       => (string) $info['name'],
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
         * Updates a Region record with new data
         *   only fields that are specified in the $info array will be written
         *
         * @param region_model $region record to be updated
         * @param array $info data to write to the record
         *
         * @return region_model updated model
         */
        public function update(region_model $region, array $info) {

            // Update record
            $updated_model = parent::_update($region, $info);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

            // Delete Cache
            // BY_COUNTRY
            if (array_key_exists('country_id', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_COUNTRY,
                        [
                            'country_id'      => (int) $region->country_id,
                        ]
                    )
                );
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_COUNTRY,
                        [
                            'country_id'      => (int) $info['country_id'],
                        ]
                    )
                );
            }

            // BY_COUNTRY_NAME_NORMALIZED
            if (array_key_exists('country_id', $info) && array_key_exists('name_normalized', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_COUNTRY_NAME_NORMALIZED,
                        [
                            'country_id'      => (int) $region->country_id,
                            'name_normalized' => (string) $region->name_normalized,
                        ]
                    )
                );
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_COUNTRY_NAME_NORMALIZED,
                        [
                            'country_id'      => (int) $info['country_id'],
                            'name_normalized' => (string) $info['name_normalized'],
                        ]
                    )
                );
            }

            // BY_ISO2
            if (array_key_exists('iso2', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_ISO2,
                        [
                            'iso2' => (string) $region->iso2,
                        ]
                    )
                );
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_ISO2,
                        [
                            'iso2' => (string) $info['iso2'],
                        ]
                    )
                );
            }

            // BY_COUNTRY_NAME
            if (array_key_exists('country_id', $info) && array_key_exists('name', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_COUNTRY_NAME,
                        [
                            'country_id' => (int) $region->country_id,
                            'name'       => (string) $region->name,
                        ]
                    )
                );
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_COUNTRY_NAME,
                        [
                            'country_id' => (int) $info['country_id'],
                            'name'       => (string) $info['name'],
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
                            'name_soundex' => (string) $region->name_soundex,
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
         * Delete a Region record
         *
         * @param region_model $region record to be deleted
         *
         * @return bool
         */
        public function delete(region_model $region) {

            // Delete record
            $return = parent::_delete($region);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

            // Delete Cache
            // BY_COUNTRY
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_COUNTRY,
                    [
                        'country_id'      => (int) $region->country_id,
                    ]
                )
            );

            // BY_COUNTRY_NAME_NORMALIZED
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_COUNTRY_NAME_NORMALIZED,
                    [
                        'country_id'      => (int) $region->country_id,
                        'name_normalized' => (string) $region->name_normalized,
                    ]
                )
            );

            // BY_ISO2
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_ISO2,
                    [
                        'iso2' => (string) $region->iso2,
                    ]
                )
            );

            // BY_COUNTRY_NAME
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_COUNTRY_NAME,
                    [
                        'country_id' => (int) $region->country_id,
                        'name'       => (string) $region->name,
                    ]
                )
            );

            // BY_NAME_SOUNDEX
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_NAME_SOUNDEX,
                    [
                        'name_soundex' => (string) $region->name_soundex,
                    ]
                )
            );

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

            return $return;
        }

        /**
         * Delete multiple Region records
         *
         * @param region_collection $region_collection records to be deleted
         *
         * @return bool
         */
        public function deletes(region_collection $region_collection) {

            // Delete records
            $return = parent::_deletes($region_collection);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

            // Delete Cache
            foreach ($region_collection as $region) {
                // BY_COUNTRY
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_COUNTRY,
                        [
                            'country_id'      => (int) $region->country_id,
                        ]
                    )
                );

                // BY_COUNTRY_NAME_NORMALIZED
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_COUNTRY_NAME_NORMALIZED,
                        [
                            'country_id'      => (int) $region->country_id,
                            'name_normalized' => (string) $region->name_normalized,
                        ]
                    )
                );

                // BY_ISO2
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_ISO2,
                        [
                            'iso2' => (string) $region->iso2,
                        ]
                    )
                );

                // BY_COUNTRY_NAME
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_COUNTRY_NAME,
                        [
                            'country_id' => (int) $region->country_id,
                            'name'       => (string) $region->name,
                        ]
                    )
                );

                // BY_NAME_SOUNDEX
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_NAME_SOUNDEX,
                        [
                            'name_soundex' => (string) $region->name_soundex,
                        ]
                    )
                );
            }

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

            return $return;
        }
    }
