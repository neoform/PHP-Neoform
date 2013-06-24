<?php

    /**
     * Locale Key Message DAO
     */
    class locale_key_message_dao extends record_dao implements locale_key_message_definition {

        const BY_LOCALE     = 'by_locale';
        const BY_LOCALE_KEY = 'by_locale_key';
        const BY_BODY       = 'by_body';
        const BY_KEY        = 'by_key';

        /**
         * Get the generic bindings of the table columns
         *
         * @return array
         */
        public static function bindings() {
            return [
                'id'     => 'int',
                'key_id' => 'int',
                'body'   => 'string',
                'locale' => 'string',
            ];
        }

        // READS

        /**
         * Get Locale Key Message ids by locale
         *
         * @param string $locale
         *
         * @return array of Locale Key Message ids
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
         * Get Locale Key Message ids by locale and key
         *
         * @param string $locale
         * @param int $key_id
         *
         * @return array of Locale Key Message ids
         */
        public static function by_locale_key($locale, $key_id) {
            return self::_by_fields(
                self::BY_LOCALE_KEY,
                [
                    'locale' => (string) $locale,
                    'key_id' => (int) $key_id,
                ]
            );
        }

        /**
         * Get Locale Key Message ids by body
         *
         * @param string $body
         *
         * @return array of Locale Key Message ids
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
         * Get Locale Key Message ids by key
         *
         * @param int $key_id
         *
         * @return array of Locale Key Message ids
         */
        public static function by_key($key_id) {
            return self::_by_fields(
                self::BY_KEY,
                [
                    'key_id' => (int) $key_id,
                ]
            );
        }

        /**
         * Get multiple sets of Locale Key Message ids by locale_key
         *
         * @param locale_key_collection|array $locale_key_list
         *
         * @return array of arrays containing Locale Key Message ids
         */
        public static function by_key_multi($locale_key_list) {
            $keys = [];
            if ($locale_key_list instanceof locale_key_collection) {
                foreach ($locale_key_list as $k => $locale_key) {
                    $keys[$k] = [
                        'key_id' => (int) $locale_key->id,
                    ];
                }
            } else {
                foreach ($locale_key_list as $k => $locale_key) {
                    $keys[$k] = [
                        'key_id' => (int) $locale_key,
                    ];
                }
            }
            return self::_by_fields_multi(self::BY_KEY, $keys);
        }

        /**
         * Get multiple sets of Locale Key Message ids by locale
         *
         * @param locale_collection|array $locale_list
         *
         * @return array of arrays containing Locale Key Message ids
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
         * Get Locale Key Message id_arr by an array of locale and keys
         *
         * @param array $locale_key_arr an array of arrays containing locales and key_ids
         *
         * @return array of arrays of Locale Key Message ids
         */
        public static function by_locale_key_multi(array $locale_key_arr) {
            $keys_arr = [];
            foreach ($locale_key_arr as $k => $locale_key) {
                $keys_arr[$k] = [
                    'locale' => (string) $locale_key['locale'],
                    'key_id' => (int) $locale_key['key_id'],
                ];
            }
            return self::_by_fields_multi(
                self::BY_LOCALE_KEY,
                $keys_arr
            );
        }

        /**
         * Get Locale Key Message id_arr by an array of bodys
         *
         * @param array $body_arr an array containing bodys
         *
         * @return array of arrays of Locale Key Message ids
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

        // WRITES

        /**
         * Insert Locale Key Message record, created from an array of $info
         *
         * @param array $info associative array, keys matching columns in database for this entity
         *
         * @return locale_key_message_model
         */
        public static function insert(array $info) {

            // Insert record
            $return = parent::_insert($info);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

            // Delete Cache
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

            // BY_LOCALE_KEY
            if (array_key_exists('locale', $info) && array_key_exists('key_id', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_LOCALE_KEY,
                        [
                            'locale' => (string) $info['locale'],
                            'key_id' => (int) $info['key_id'],
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

            // BY_KEY
            if (array_key_exists('key_id', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_KEY,
                        [
                            'key_id' => (int) $info['key_id'],
                        ]
                    )
                );
            }

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

            return $return;
        }

        /**
         * Insert multiple Locale Key Message records, created from an array of arrays of $info
         *
         * @param array $infos array of associative arrays, keys matching columns in database for this entity
         *
         * @return locale_key_message_collection
         */
        public static function inserts(array $infos) {

            // Insert records
            $return = parent::_inserts($infos);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

            // Delete Cache
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

                // BY_LOCALE_KEY
                if (array_key_exists('locale', $info) && array_key_exists('key_id', $info)) {
                    parent::_cache_delete(
                        parent::_build_key(
                            self::BY_LOCALE_KEY,
                            [
                                'locale' => (string) $info['locale'],
                                'key_id' => (int) $info['key_id'],
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

                // BY_KEY
                if (array_key_exists('key_id', $info)) {
                    parent::_cache_delete(
                        parent::_build_key(
                            self::BY_KEY,
                            [
                                'key_id' => (int) $info['key_id'],
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
         * Updates a Locale Key Message record with new data
         *   only fields that are specified in the $info array will be written
         *
         * @param locale_key_message_model $locale_key_message record to be updated
         * @param array $info data to write to the record
         *
         * @return locale_key_message_model updated model
         */
        public static function update(locale_key_message_model $locale_key_message, array $info) {

            // Update record
            $updated_model = parent::_update($locale_key_message, $info);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

            // Delete Cache
            // BY_LOCALE
            if (array_key_exists('locale', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_LOCALE,
                        [
                            'locale' => (string) $locale_key_message->locale,
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

            // BY_LOCALE_KEY
            if (array_key_exists('locale', $info) && array_key_exists('key_id', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_LOCALE_KEY,
                        [
                            'locale' => (string) $locale_key_message->locale,
                            'key_id' => (int) $locale_key_message->key_id,
                        ]
                    )
                );
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_LOCALE_KEY,
                        [
                            'locale' => (string) $info['locale'],
                            'key_id' => (int) $info['key_id'],
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
                            'body' => (string) $locale_key_message->body,
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

            // BY_KEY
            if (array_key_exists('key_id', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_KEY,
                        [
                            'key_id' => (int) $locale_key_message->key_id,
                        ]
                    )
                );
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_KEY,
                        [
                            'key_id' => (int) $info['key_id'],
                        ]
                    )
                );
            }

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

            return $updated_model;
        }

        /**
         * Delete a Locale Key Message record
         *
         * @param locale_key_message_model $locale_key_message record to be deleted
         *
         * @return bool
         */
        public static function delete(locale_key_message_model $locale_key_message) {

            // Delete record
            $return = parent::_delete($locale_key_message);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

            // Delete Cache
            // BY_LOCALE
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_LOCALE,
                    [
                        'locale' => (string) $locale_key_message->locale,
                    ]
                )
            );

            // BY_LOCALE_KEY
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_LOCALE_KEY,
                    [
                        'locale' => (string) $locale_key_message->locale,
                        'key_id' => (int) $locale_key_message->key_id,
                    ]
                )
            );

            // BY_BODY
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_BODY,
                    [
                        'body' => (string) $locale_key_message->body,
                    ]
                )
            );

            // BY_KEY
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_KEY,
                    [
                        'key_id' => (int) $locale_key_message->key_id,
                    ]
                )
            );

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

            return $return;
        }

        /**
         * Delete multiple Locale Key Message records
         *
         * @param locale_key_message_collection $locale_key_message_collection records to be deleted
         *
         * @return bool
         */
        public static function deletes(locale_key_message_collection $locale_key_message_collection) {

            // Delete records
            $return = parent::_deletes($locale_key_message_collection);

            // Batch all cache deletion into one pipelined request to the cache engine (if supported by cache engine)
            parent::cache_batch_start();

            // Delete Cache
            foreach ($locale_key_message_collection as $locale_key_message) {
                // BY_LOCALE
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_LOCALE,
                        [
                            'locale' => (string) $locale_key_message->locale,
                        ]
                    )
                );

                // BY_LOCALE_KEY
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_LOCALE_KEY,
                        [
                            'locale' => (string) $locale_key_message->locale,
                            'key_id' => (int) $locale_key_message->key_id,
                        ]
                    )
                );

                // BY_BODY
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_BODY,
                        [
                            'body' => (string) $locale_key_message->body,
                        ]
                    )
                );

                // BY_KEY
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_KEY,
                        [
                            'key_id' => (int) $locale_key_message->key_id,
                        ]
                    )
                );
            }

            // Execute pipelined cache deletion queries (if supported by cache engine)
            parent::cache_batch_execute();

            return $return;
        }
    }
