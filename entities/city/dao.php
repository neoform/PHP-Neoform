<?php

    /**
     * City DAO
     */
    class city_dao extends record_dao implements city_definition {

        const BY_NAME_NORMALIZED        = 'by_name_normalized';
        const BY_NAME_SOUNDEX           = 'by_name_soundex';
        const BY_REGION                 = 'by_region';
        const BY_REGION_NAME_NORMALIZED = 'by_region_name_normalized';

        // READS

        /**
         * Get City ids by name_normalized
         *
         * @param string $name_normalized
         *
         * @return array of City ids
         */
        public static function by_name_normalized($name_normalized) {
            return self::_by_fields(
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
        public static function by_name_soundex($name_soundex) {
            return self::_by_fields(
                self::BY_NAME_SOUNDEX,
                [
                    'name_soundex' => (string) $name_soundex,
                ]
            );
        }

        /**
         * Get City ids by region
         *
         * @param int $region_id
         *
         * @return array of City ids
         */
        public static function by_region($region_id) {
            return self::_by_fields(
                self::BY_REGION,
                [
                    'region_id' => (int) $region_id,
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
        public static function by_region_name_normalized($region_id, $name_normalized) {
            return self::_by_fields(
                self::BY_REGION_NAME_NORMALIZED,
                [
                    'region_id'       => (int) $region_id,
                    'name_normalized' => (string) $name_normalized,
                ]
            );
        }

        /**
         * Get multiple sets of City ids by region
         *
         * @param region_collection $region_collection
         *
         * @return array of arrays containing City ids
         */
        public static function by_region_multi(region_collection $region_collection) {
            $keys = [];
            foreach ($region_collection as $k => $region) {
                $keys[$k] = [
                    'region_id' => (int) $region->id,
                ];
            }
            return self::_by_fields_multi(self::BY_REGION, $keys);
        }

        // WRITES

        /**
         * Insert City record, created from an array of $info
         *
         * @param array $info associative array, keys matching columns in database for this entity
         *
         * @return city_model
         */
        public static function insert(array $info) {
            $return = parent::_insert($info);

            // Delete Cache
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

            return $return;
        }

        /**
         * Insert multiple City records, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return city_collection
         */
        public static function inserts(array $infos) {
            $return = parent::_inserts($infos);

            // Delete Cache
            foreach ($infos as $info) {
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

            }

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
        public static function update(city_model $city, array $info) {
            $updated_model = parent::_update($city, $info);

            // Delete Cache
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

            // BY_REGION
            if (array_key_exists('region_id', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_REGION,
                        [
                            'region_id' => (int) $city->region_id,
                        ]
                    )
                );
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

            return $updated_model;
        }

        /**
         * Delete a City record
         *
         * @param city_model $city record to be deleted
         *
         * @return bool
         */
        public static function delete(city_model $city) {
            $return = parent::_delete($city);

            // Delete Cache
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

            return $return;
        }

        /**
         * Delete multiple City records
         *
         * @param city_collection $city_collection records to be deleted
         *
         * @return bool
         */
        public static function deletes(city_collection $city_collection) {
            $return = parent::_deletes($city_collection);

            // Delete Cache
            foreach ($city_collection as $city) {
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

            }

            return $return;
        }

    }
