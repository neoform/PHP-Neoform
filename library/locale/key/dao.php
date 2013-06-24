<?php

    /**
     * Locale Key DAO
     */
    class locale_key_dao extends record_dao implements locale_key_definition {

        const BY_ALL       = 'by_all';
        const BY_LOCALE    = 'by_locale';
        const BY_BODY      = 'by_body';
        const BY_NAMESPACE = 'by_namespace';

        /**
         * Get the generic bindings of the table columns
         *
         * @return array
         */
        public static function bindings() {
            return [
                'id'           => 'int',
                'body'         => 'string',
                'locale'       => 'string',
                'namespace_id' => 'int',
            ];
        }

        // READS

        /**
         * Get Locale Key ids by locale
         *
         * @param string $locale
         *
         * @return array of Locale Key ids
         */
        public static function by_locale($locale) {
            return self::_by_fields(
                self::BY_LOCALE,
                [
                    'locale' => (string) $locale,
                ]
            );
        }

        /**
         * Get Locale Key ids by body
         *
         * @param string $body
         *
         * @return array of Locale Key ids
         */
        public static function by_body($body) {
            return self::_by_fields(
                self::BY_BODY,
                [
                    'body' => (string) $body,
                ]
            );
        }

        /**
         * Get Locale Key ids by namespace
         *
         * @param int $namespace_id
         *
         * @return array of Locale Key ids
         */
        public static function by_namespace($namespace_id) {
            return self::_by_fields(
                self::BY_NAMESPACE,
                [
                    'namespace_id' => (int) $namespace_id,
                ]
            );
        }

        /**
         * Get multiple sets of Locale Key ids by locale
         *
         * @param locale_collection|array $locale_list
         *
         * @return array of arrays containing Locale Key ids
         */
        public static function by_locale_multi($locale_list) {
            $keys = [];
            if ($locale_list instanceof locale_collection) {
                foreach ($locale_list as $k => $locale) {
                    $keys[$k] = [
                        'locale' => (string) $locale->iso2,
                    ];
                }
            } else {
                foreach ($locale_list as $k => $locale) {
                    $keys[$k] = [
                        'locale' => (string) $locale,
                    ];
                }
            }
            return self::_by_fields_multi(self::BY_LOCALE, $keys);
        }

        /**
         * Get multiple sets of Locale Key ids by locale_namespace
         *
         * @param locale_namespace_collection|array $locale_namespace_list
         *
         * @return array of arrays containing Locale Key ids
         */
        public static function by_namespace_multi($locale_namespace_list) {
            $keys = [];
            if ($locale_namespace_list instanceof locale_namespace_collection) {
                foreach ($locale_namespace_list as $k => $locale_namespace) {
                    $keys[$k] = [
                        'namespace_id' => (int) $locale_namespace->id,
                    ];
                }
            } else {
                foreach ($locale_namespace_list as $k => $locale_namespace) {
                    $keys[$k] = [
                        'namespace_id' => (int) $locale_namespace,
                    ];
                }
            }
            return self::_by_fields_multi(self::BY_NAMESPACE, $keys);
        }

        /**
         * Get Locale Key id_arr by an array of bodys
         *
         * @param array $body_arr an array containing bodys
         *
         * @return array of arrays of Locale Key ids
         */
        public static function by_body_multi(array $body_arr) {
            $keys_arr = [];
            foreach ($body_arr as $k => $body) {
                $keys_arr[$k] = [ 'body' => (string) $body, ];
            }
            return self::_by_fields_multi(
                self::BY_BODY,
                $keys_arr
            );
        }

        /**
         * Get all data for all Locale Key records
         *
         * @return array containing all Locale Key records
         */
        public static function all() {
            return parent::_all(self::BY_ALL);
        }

        // WRITES

        /**
         * Insert Locale Key record, created from an array of $info
         *
         * @param array $info associative array, keys matching columns in database for this entity
         *
         * @return locale_key_model
         */
        public static function insert(array $info) {

            // Insert record
            $return = parent::_insert($info);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

            // Delete Cache
            // BY_ALL
            parent::_cache_delete(
                parent::_build_key(self::BY_ALL)
            );

            // BY_LOCALE
            if (array_key_exists('locale', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_LOCALE,
                        [
                            'locale' => (string) $info['locale'],
                        ]
                    )
                );
            }

            // BY_BODY
            if (array_key_exists('body', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_BODY,
                        [
                            'body' => (string) $info['body'],
                        ]
                    )
                );
            }

            // BY_NAMESPACE
            if (array_key_exists('namespace_id', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_NAMESPACE,
                        [
                            'namespace_id' => (int) $info['namespace_id'],
                        ]
                    )
                );
            }

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

            return $return;
        }

        /**
         * Insert multiple Locale Key records, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return locale_key_collection
         */
        public static function inserts(array $infos) {

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
                // BY_LOCALE
                if (array_key_exists('locale', $info)) {
                    parent::_cache_delete(
                        parent::_build_key(
                            self::BY_LOCALE,
                            [
                                'locale' => (string) $info['locale'],
                            ]
                        )
                    );
                }

                // BY_BODY
                if (array_key_exists('body', $info)) {
                    parent::_cache_delete(
                        parent::_build_key(
                            self::BY_BODY,
                            [
                                'body' => (string) $info['body'],
                            ]
                        )
                    );
                }

                // BY_NAMESPACE
                if (array_key_exists('namespace_id', $info)) {
                    parent::_cache_delete(
                        parent::_build_key(
                            self::BY_NAMESPACE,
                            [
                                'namespace_id' => (int) $info['namespace_id'],
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
         * Updates a Locale Key record with new data
         *   only fields that are specified in the $info array will be written
         *
         * @param locale_key_model $locale_key record to be updated
         * @param array $info data to write to the record
         *
         * @return locale_key_model updated model
         */
        public static function update(locale_key_model $locale_key, array $info) {

            // Update record
            $updated_model = parent::_update($locale_key, $info);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

            // Delete Cache
            // BY_ALL
            parent::_cache_delete(
                parent::_build_key(self::BY_ALL)
            );

            // BY_LOCALE
            if (array_key_exists('locale', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_LOCALE,
                        [
                            'locale' => (string) $locale_key->locale,
                        ]
                    )
                );
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_LOCALE,
                        [
                            'locale' => (string) $info['locale'],
                        ]
                    )
                );
            }

            // BY_BODY
            if (array_key_exists('body', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_BODY,
                        [
                            'body' => (string) $locale_key->body,
                        ]
                    )
                );
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_BODY,
                        [
                            'body' => (string) $info['body'],
                        ]
                    )
                );
            }

            // BY_NAMESPACE
            if (array_key_exists('namespace_id', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_NAMESPACE,
                        [
                            'namespace_id' => (int) $locale_key->namespace_id,
                        ]
                    )
                );
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_NAMESPACE,
                        [
                            'namespace_id' => (int) $info['namespace_id'],
                        ]
                    )
                );
            }

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

            return $updated_model;
        }

        /**
         * Delete a Locale Key record
         *
         * @param locale_key_model $locale_key record to be deleted
         *
         * @return bool
         */
        public static function delete(locale_key_model $locale_key) {

            // Delete record
            $return = parent::_delete($locale_key);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

            // Delete Cache
            // BY_ALL
            parent::_cache_delete(
                parent::_build_key(self::BY_ALL)
            );

            // BY_LOCALE
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_LOCALE,
                    [
                        'locale' => (string) $locale_key->locale,
                    ]
                )
            );

            // BY_BODY
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_BODY,
                    [
                        'body' => (string) $locale_key->body,
                    ]
                )
            );

            // BY_NAMESPACE
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_NAMESPACE,
                    [
                        'namespace_id' => (int) $locale_key->namespace_id,
                    ]
                )
            );

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

            return $return;
        }

        /**
         * Delete multiple Locale Key records
         *
         * @param locale_key_collection $locale_key_collection records to be deleted
         *
         * @return bool
         */
        public static function deletes(locale_key_collection $locale_key_collection) {

            // Delete records
            $return = parent::_deletes($locale_key_collection);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

            // Delete Cache
            // BY_ALL
            parent::_cache_delete(
                parent::_build_key(self::BY_ALL)
            );

            foreach ($locale_key_collection as $locale_key) {
                // BY_LOCALE
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_LOCALE,
                        [
                            'locale' => (string) $locale_key->locale,
                        ]
                    )
                );

                // BY_BODY
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_BODY,
                        [
                            'body' => (string) $locale_key->body,
                        ]
                    )
                );

                // BY_NAMESPACE
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_NAMESPACE,
                        [
                            'namespace_id' => (int) $locale_key->namespace_id,
                        ]
                    )
                );
            }

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

            return $return;
        }
    }
