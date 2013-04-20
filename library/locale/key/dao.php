<?php

    class locale_key_dao extends record_dao implements locale_key_definition {

        const BY_ALL        = 'by_all';
        const BY_LOCALE     = 'by_locale';
        const BY_BODY       = 'by_body';
        const BY_NAMESPACE  = 'by_namespace';

        public static function castings() {
            return [
                'id'           => 'int',
                'body'         => 'string',
                'locale'       => 'string',
                'namespace_id' => 'int',
            ];
        }

        // READS

        public static function all() {
            return parent::_all(self::BY_ALL);
        }

        public static function by_locale($locale) {
            return self::_by_fields(
                self::BY_LOCALE,
                [
                    'locale' => (string) $locale,
                ]
            );
        }

        public static function by_body($body) {
            return self::_by_fields(
                self::BY_BODY,
                [
                    'body' => (string) $body,
                ]
            );
        }

        public static function by_namespace($namespace_id) {
            return self::_by_fields(
                self::BY_NAMESPACE,
                [
                    'namespace_id' => (int) $namespace_id,
                ]
            );
        }

        public static function by_locale_multi(locale_collection $locale_collection) {
            $keys = [];
            foreach ($locale_collection as $k => $locale) {
                $keys[$k] = [
                    'locale' => (string) $locale->iso2,
                ];
            }
            return self::_by_fields_multi(self::BY_LOCALE, $keys);
        }

        public static function by_namespace_multi(locale_namespace_collection $locale_namespace_collection) {
            $keys = [];
            foreach ($locale_namespace_collection as $k => $locale_namespace) {
                $keys[$k] = [
                    'namespace_id' => (int) $locale_namespace->id,
                ];
            }
            return self::_by_fields_multi(self::BY_NAMESPACE, $keys);
        }

        // WRITES

        public static function insert(array $info) {
            $return = parent::_insert($info);

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

            return $return;
        }

        public static function inserts(array $infos) {
            $return = parent::_inserts($infos);

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

            // BY_ALL
            parent::_cache_delete(
                parent::_build_key(self::BY_ALL)
            );

            return $return;
        }

        public static function update(locale_key_model $locale_key, array $info) {
            $updated_model = parent::_update($locale_key, $info);

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

            return $updated_model;
        }

        public static function delete(locale_key_model $locale_key) {
            $return = parent::_delete($locale_key);

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

            return $return;
        }

        public static function deletes(locale_key_collection $locale_keys) {
            $return = parent::_deletes($locale_keys);

            // Delete Cache
            foreach ($locale_keys as $locale_key) {
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

            // BY_ALL
            parent::_cache_delete(
                parent::_build_key(self::BY_ALL)
            );

            return $return;
        }
    }
