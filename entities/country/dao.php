<?php

    /**
     * Country DAO
     */
    class country_dao extends record_dao implements country_definition {

        const BY_ALL             = 'by_all';
        const BY_NAME_NORMALIZED = 'by_name_normalized';
        const BY_ISO2            = 'by_iso2';
        const BY_ISO3            = 'by_iso3';
        const BY_NAME            = 'by_name';

        /**
         * Caching engines used by this entity
         *
         * @return int
         */
        protected static function _cache_engines() {
            return cache_lib::MC;
        }

        // READS

        /**
         * Get Country ids by name_normalized
         *
         * @param string $name_normalized
         *
         * @return array of Country ids
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
         * Get Country ids by iso2
         *
         * @param string $iso2
         *
         * @return array of Country ids
         */
        public static function by_iso2($iso2) {
            return self::_by_fields(
                self::BY_ISO2,
                [
                    'iso2' => (string) $iso2,
                ]
            );
        }

        /**
         * Get Country ids by iso3
         *
         * @param string $iso3
         *
         * @return array of Country ids
         */
        public static function by_iso3($iso3) {
            return self::_by_fields(
                self::BY_ISO3,
                [
                    'iso3' => (string) $iso3,
                ]
            );
        }

        /**
         * Get Country ids by name
         *
         * @param string $name
         *
         * @return array of Country ids
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
         * Get all data for all Country records
         *
         * @return array containing all Country records
         */
        public static function all() {
            return parent::_all(self::BY_ALL);
        }

        // WRITES

        /**
         * Insert Country record, created from an array of $info
         *
         * @param array $info associative array, keys matching columns in database for this entity
         *
         * @return country_model
         */
        public static function insert(array $info) {
            $return = parent::_insert($info);

            // Delete Cache
            // BY_ALL
            parent::_cache_delete(
                parent::_build_key(self::BY_ALL)
            );

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

            // BY_ISO3
            if (array_key_exists('iso3', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_ISO3,
                        [
                            'iso3' => (string) $info['iso3'],
                        ]
                    )
                );
            }

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
         * Insert multiple Country records, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return country_collection
         */
        public static function inserts(array $infos) {
            $return = parent::_inserts($infos);

            // Delete Cache
            // BY_ALL
            parent::_cache_delete(
                parent::_build_key(self::BY_ALL)
            );

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

                // BY_ISO3
                if (array_key_exists('iso3', $info)) {
                    parent::_cache_delete(
                        parent::_build_key(
                            self::BY_ISO3,
                            [
                                'iso3' => (string) $info['iso3'],
                            ]
                        )
                    );
                }

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
         * Updates a Country record with new data
         *   only fields that are specified in the $info array will be written
         *
         * @param country_model $country record to be updated
         * @param array $info data to write to the record
         *
         * @return country_model updated model
         */
        public static function update(country_model $country, array $info) {
            $updated_model = parent::_update($country, $info);

            // Delete Cache
            // BY_ALL
            parent::_cache_delete(
                parent::_build_key(self::BY_ALL)
            );

            // BY_NAME_NORMALIZED
            if (array_key_exists('name_normalized', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_NAME_NORMALIZED,
                        [
                            'name_normalized' => (string) $country->name_normalized,
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

            // BY_ISO2
            if (array_key_exists('iso2', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_ISO2,
                        [
                            'iso2' => (string) $country->iso2,
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

            // BY_ISO3
            if (array_key_exists('iso3', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_ISO3,
                        [
                            'iso3' => (string) $country->iso3,
                        ]
                    )
                );
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_ISO3,
                        [
                            'iso3' => (string) $info['iso3'],
                        ]
                    )
                );
            }

            // BY_NAME
            if (array_key_exists('name', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_NAME,
                        [
                            'name' => (string) $country->name,
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
         * Delete a Country record
         *
         * @param country_model $country record to be deleted
         *
         * @return bool
         */
        public static function delete(country_model $country) {
            $return = parent::_delete($country);

            // Delete Cache
            // BY_ALL
            parent::_cache_delete(
                parent::_build_key(self::BY_ALL)
            );

            // BY_NAME_NORMALIZED
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_NAME_NORMALIZED,
                    [
                        'name_normalized' => (string) $country->name_normalized,
                    ]
                )
            );

            // BY_ISO2
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_ISO2,
                    [
                        'iso2' => (string) $country->iso2,
                    ]
                )
            );

            // BY_ISO3
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_ISO3,
                    [
                        'iso3' => (string) $country->iso3,
                    ]
                )
            );

            // BY_NAME
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_NAME,
                    [
                        'name' => (string) $country->name,
                    ]
                )
            );

            return $return;
        }

        /**
         * Delete multiple Country records
         *
         * @param country_collection $country_collection records to be deleted
         *
         * @return bool
         */
        public static function deletes(country_collection $country_collection) {
            $return = parent::_deletes($country_collection);

            // Delete Cache
            // BY_ALL
            parent::_cache_delete(
                parent::_build_key(self::BY_ALL)
            );

            foreach ($country_collection as $country) {
                // BY_NAME_NORMALIZED
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_NAME_NORMALIZED,
                        [
                            'name_normalized' => (string) $country->name_normalized,
                        ]
                    )
                );

                // BY_ISO2
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_ISO2,
                        [
                            'iso2' => (string) $country->iso2,
                        ]
                    )
                );

                // BY_ISO3
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_ISO3,
                        [
                            'iso3' => (string) $country->iso3,
                        ]
                    )
                );

                // BY_NAME
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_NAME,
                        [
                            'name' => (string) $country->name,
                        ]
                    )
                );

            }

            return $return;
        }

    }
