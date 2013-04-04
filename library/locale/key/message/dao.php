<?php

    class locale_key_message_dao extends record_dao implements locale_key_message_definition {

        const BY_BODY       = 'by_body';
        const BY_KEY        = 'by_key';
        const BY_LOCALE     = 'by_locale';
        const BY_LOCALE_KEY = 'by_locale_key';

        // READS

        public static function by_body($body) {
            return self::_by_fields(
                self::BY_BODY,
                array(
                    'body' => (string) $body,
                )
            );
        }

        public static function by_key($key_id) {
            return self::_by_fields(
                self::BY_KEY,
                array(
                    'key_id' => (int) $key_id,
                )
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

        public static function by_locale_key($locale, $key_id) {
            return self::_by_fields(
                self::BY_LOCALE_KEY,
                array(
                    'locale' => (string) $locale,
                    'key_id' => (int) $key_id,
                )
            );
        }

        public static function by_key_multi(locale_key_collection $locale_key_collection) {
            $keys = array();
            foreach ($locale_key_collection as $k => $locale_key) {
                $keys[$k] = array(
                    'key_id' => (int) $locale_key->id,
                );
            }
            return self::_by_fields_multi(self::BY_KEY, $keys);
        }

        public static function by_locale_multi(locale_collection $locale_collection) {
            $keys = array();
            foreach ($locale_collection as $k => $locale) {
                $keys[$k] = array(
                    'locale' => (string) $locale->iso2,
                );
            }
            return self::_by_fields_multi(self::BY_LOCALE, $keys);
        }

        // WRITES

        public static function insert(array $info) {
            $return = parent::_insert($info);

            // Delete Cache
            // BY_BODY
            if (array_key_exists('body', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_BODY,
                        array(
                            'body' => (string) $info['body'],
                        )
                    )
                );
            }

            // BY_KEY
            if (array_key_exists('key_id', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_KEY,
                        array(
                            'key_id' => (int) $info['key_id'],
                        )
                    )
                );
            }

            // BY_LOCALE
            if (array_key_exists('locale', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_LOCALE,
                        array(
                            'locale' => (string) $info['locale'],
                        )
                    )
                );
            }

            // BY_LOCALE_KEY
            if (array_key_exists('locale', $info) && array_key_exists('key_id', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_LOCALE_KEY,
                        array(
                            'locale' => (string) $info['locale'],
                            'key_id' => (int) $info['key_id'],
                        )
                    )
                );
            }

            return $return;
        }

        public static function inserts(array $infos) {
            $return = parent::_inserts($infos);

            // Delete Cache
            foreach ($infos as $info) {
                // BY_BODY
                if (array_key_exists('body', $info)) {
                    parent::_cache_delete(
                        parent::_build_key(
                            self::BY_BODY,
                            array(
                                'body' => (string) $info['body'],
                            )
                        )
                    );
                }

                // BY_KEY
                if (array_key_exists('key_id', $info)) {
                    parent::_cache_delete(
                        parent::_build_key(
                            self::BY_KEY,
                            array(
                                'key_id' => (int) $info['key_id'],
                            )
                        )
                    );
                }

                // BY_LOCALE
                if (array_key_exists('locale', $info)) {
                    parent::_cache_delete(
                        parent::_build_key(
                            self::BY_LOCALE,
                            array(
                                'locale' => (string) $info['locale'],
                            )
                        )
                    );
                }

                // BY_LOCALE_KEY
                if (array_key_exists('locale', $info) && array_key_exists('key_id', $info)) {
                    parent::_cache_delete(
                        parent::_build_key(
                            self::BY_LOCALE_KEY,
                            array(
                                'locale' => (string) $info['locale'],
                                'key_id' => (int) $info['key_id'],
                            )
                        )
                    );
                }

            }

            return $return;
        }

        public static function update(locale_key_message_model $locale_key_message, array $info) {
            $updated_model = parent::_update($locale_key_message, $info);

            // Delete Cache
            // BY_BODY
            if (array_key_exists('body', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_BODY,
                        array(
                            'body' => (string) $locale_key_message->body,
                        )
                    )
                );
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_BODY,
                        array(
                            'body' => (string) $info['body'],
                        )
                    )
                );
            }

            // BY_KEY
            if (array_key_exists('key_id', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_KEY,
                        array(
                            'key_id' => (int) $locale_key_message->key_id,
                        )
                    )
                );
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_KEY,
                        array(
                            'key_id' => (int) $info['key_id'],
                        )
                    )
                );
            }

            // BY_LOCALE
            if (array_key_exists('locale', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_LOCALE,
                        array(
                            'locale' => (string) $locale_key_message->locale,
                        )
                    )
                );
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_LOCALE,
                        array(
                            'locale' => (string) $info['locale'],
                        )
                    )
                );
            }

            // BY_LOCALE_KEY
            if (array_key_exists('locale', $info) && array_key_exists('key_id', $info)) {
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_LOCALE_KEY,
                        array(
                            'locale' => (string) $locale_key_message->locale,
                            'key_id' => (int) $locale_key_message->key_id,
                        )
                    )
                );
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_LOCALE_KEY,
                        array(
                            'locale' => (string) $info['locale'],
                            'key_id' => (int) $info['key_id'],
                        )
                    )
                );
            }

            return $updated_model;
        }

        public static function delete(locale_key_message_model $locale_key_message) {
            $return = parent::_delete($locale_key_message);

            // Delete Cache
            // BY_BODY
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_BODY,
                    array(
                        'body' => (string) $locale_key_message->body,
                    )
                )
            );

            // BY_KEY
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_KEY,
                    array(
                        'key_id' => (int) $locale_key_message->key_id,
                    )
                )
            );

            // BY_LOCALE
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_LOCALE,
                    array(
                        'locale' => (string) $locale_key_message->locale,
                    )
                )
            );

            // BY_LOCALE_KEY
            parent::_cache_delete(
                parent::_build_key(
                    self::BY_LOCALE_KEY,
                    array(
                        'locale' => (string) $locale_key_message->locale,
                        'key_id' => (int) $locale_key_message->key_id,
                    )
                )
            );

            return $return;
        }

        public static function deletes(locale_key_message_collection $locale_key_messages) {
            $return = parent::_deletes($locale_key_messages);

            // Delete Cache
            foreach ($locale_key_messages as $locale_key_message) {
                // BY_BODY
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_BODY,
                        array(
                            'body' => (string) $locale_key_message->body,
                        )
                    )
                );

                // BY_KEY
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_KEY,
                        array(
                            'key_id' => (int) $locale_key_message->key_id,
                        )
                    )
                );

                // BY_LOCALE
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_LOCALE,
                        array(
                            'locale' => (string) $locale_key_message->locale,
                        )
                    )
                );

                // BY_LOCALE_KEY
                parent::_cache_delete(
                    parent::_build_key(
                        self::BY_LOCALE_KEY,
                        array(
                            'locale' => (string) $locale_key_message->locale,
                            'key_id' => (int) $locale_key_message->key_id,
                        )
                    )
                );

            }

            return $return;
        }

    }
