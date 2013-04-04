<?php

    throw new exception('IT IS USED! :o');

    class locale_message_dao extends record_dao implements locale_message_definition {

        const BY_PARENT           = 'by_parent';
        const BY_BODY             = 'by_body';
        const BY_LOCALE           = 'by_locale';
        const BY_LOCALE_NAMESPACE = 'by_locale_namespace';
        const BY_NAMESPACE        = 'by_namespace';

        // READS

        public static function by_parent($parent_id) {
            return self::_by_fields(
                self::BY_PARENT,
                [
                    'parent_id' => (int) $parent_id,
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

        public static function by_locale($locale) {
            return self::_by_fields(
                self::BY_LOCALE,
                array(
                    'locale' => (string) $locale,
                )
            );
        }

        public static function by_locale_namespace($locale, $namespace) {
            return self::_by_fields(
                self::BY_LOCALE_NAMESPACE,
                [
                    'locale'    => (string) $locale,
                    'namespace' => (int) $namespace,
                ]
            );
        }

        public static function by_namespace($namespace) {
            return self::_by_fields(
                self::BY_NAMESPACE,
                [
                    'namespace' => (int) $namespace,
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
                    'namespace' => (int) $locale_namespace->id,
                ];
            }
            return self::_by_fields_multi(self::BY_NAMESPACE, $keys);
        }

        public static function by_parent_multi(locale_key_collection $locale_key_collection) {
            $keys = [];
            foreach ($locale_key_collection as $k => $locale_key) {
                $keys[$k] = [
                    'parent_id' => (int) $locale_key->id,
                ];
            }
            return self::_by_fields_multi(self::BY_PARENT, $keys);
        }

        // WRITES

        public static function insert(array $info) {
            $return = parent::_insert($info);

            // Delete Cache
            // BY_PARENT
            if (array_key_exists('parent_id', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_PARENT,
                        [
                            'parent_id' => (int) $info['parent_id'],
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

            // BY_LOCALE_NAMESPACE
            if (array_key_exists('locale', $info) && array_key_exists('namespace', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_LOCALE_NAMESPACE,
                        [
                            'locale'    => (string) $info['locale'],
                            'namespace' => (int) $info['namespace'],
                        ]
                    )
                );
            }

            // BY_NAMESPACE
            if (array_key_exists('namespace', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_NAMESPACE,
                        [
                            'namespace' => (int) $info['namespace'],
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
                // BY_PARENT
                if (array_key_exists('parent_id', $info)) {
                    parent::_cache_delete(
                        parent::_build_key(
                            self::BY_PARENT,
                            [
                                'parent_id' => (int) $info['parent_id'],
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

                // BY_LOCALE_NAMESPACE
                if (array_key_exists('locale', $info) && array_key_exists('namespace', $info)) {
                    parent::_cache_delete(
                        parent::_build_key(
                            self::BY_LOCALE_NAMESPACE,
                            [
                                'locale'    => (string) $info['locale'],
                                'namespace' => (int) $info['namespace'],
                            ]
                        )
                    );
                }

                // BY_NAMESPACE
                if (array_key_exists('namespace', $info)) {
                    parent::_cache_delete(
                        parent::_build_key(
                            self::BY_NAMESPACE,
                            [
                                'namespace' => (int) $info['namespace'],
                            ]
                        )
                    );
                }

            }

            return $return;
        }

        public static function update(locale_message_model $locale_message, array $info) {
            $updated_model = parent::_update($locale_message, $info);

            // Delete Cache
            // BY_PARENT
            if (array_key_exists('parent_id', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_PARENT,
                        [
                            'parent_id' => (int) $locale_message->parent_id,
                        ]
                    )
                );
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_PARENT,
                        [
                            'parent_id' => (int) $info['parent_id'],
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
                            'body' => (string) $locale_message->body,
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

            // BY_LOCALE
            if (array_key_exists('locale', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_LOCALE,
                        [
                            'locale' => (string) $locale_message->locale,
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

            // BY_LOCALE_NAMESPACE
            if (array_key_exists('locale', $info) && array_key_exists('namespace', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_LOCALE_NAMESPACE,
                        [
                            'locale'    => (string) $locale_message->locale,
                            'namespace' => (int) $locale_message->namespace,
                        ]
                    )
                );
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_LOCALE_NAMESPACE,
                        [
                            'locale'    => (string) $info['locale'],
                            'namespace' => (int) $info['namespace'],
                        ]
                    )
                );
            }

            // BY_NAMESPACE
            if (array_key_exists('namespace', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_NAMESPACE,
                        [
                            'namespace' => (int) $locale_message->namespace,
                        ]
                    )
                );
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_NAMESPACE,
                        [
                            'namespace' => (int) $info['namespace'],
                        ]
                    )
                );
            }

            return $updated_model;
        }

        public static function delete(locale_message_model $locale_message) {
            $return = parent::_delete($locale_message);

            // Delete Cache
            // BY_PARENT
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_PARENT,
                    [
                        'parent_id' => (int) $locale_message->parent_id,
                    ]
                )
            );

            // BY_BODY
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_BODY,
                    [
                        'body' => (string) $locale_message->body,
                    ]
                )
            );

            // BY_LOCALE
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_LOCALE,
                    [
                        'locale' => (string) $locale_message->locale,
                    ]
                )
            );

            // BY_LOCALE_NAMESPACE
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_LOCALE_NAMESPACE,
                    [
                        'locale'    => (string) $locale_message->locale,
                        'namespace' => (int) $locale_message->namespace,
                    ]
                )
            );

            // BY_NAMESPACE
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_NAMESPACE,
                    [
                        'namespace' => (int) $locale_message->namespace,
                    ]
                )
            );

            return $return;
        }

        public static function deletes(locale_message_collection $locale_messages) {
            $return = parent::_deletes($locale_messages);

            // Delete Cache
            foreach ($locale_messages as $locale_message) {
                // BY_PARENT
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_PARENT,
                        [
                            'parent_id' => (int) $locale_message->parent_id,
                        ]
                    )
                );

                // BY_BODY
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_BODY,
                        [
                            'body' => (string) $locale_message->body,
                        ]
                    )
                );

                // BY_LOCALE
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_LOCALE,
                        [
                            'locale' => (string) $locale_message->locale,
                        ]
                    )
                );

                // BY_LOCALE_NAMESPACE
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_LOCALE_NAMESPACE,
                        [
                            'locale'    => (string) $locale_message->locale,
                            'namespace' => (int) $locale_message->namespace,
                        ]
                    )
                );

                // BY_NAMESPACE
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_NAMESPACE,
                        [
                            'namespace' => (int) $locale_message->namespace,
                        ]
                    )
                );
            }

            return $return;
        }
    }
