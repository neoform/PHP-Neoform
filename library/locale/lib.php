<?php

    class locale_lib {

        /**
         * @param string  $locale
         * @param integer $namespace_id
         *
         * @return string
         */
        public static function by_locale_namespace_key($locale, $namespace_id) {
            return "locale_lib:by_locale_namespace:{$locale}" . ($namespace_id ? ":{$namespace_id}" : '');
        }

        /**
         * Get translation dictionary by iso2 and namespace
         *
         * @param string  $locale_iso2
         * @param integer $namespace_id
         *
         * @return array
         */
        public static function by_locale_namespace($locale_iso2, $namespace_id) {
            static $config;

            if (! $config) {
                $config = core::config()->system['locale'];
            }

            $get = function() use ($locale_iso2, $namespace_id) {
                $messages = core::sql('slave')->prepare("
                    SELECT
                        locale_key.body k,
                        locale_key_message.body v
                    FROM
                        locale_key_message
                    INNER JOIN
                        locale_key
                    ON
                        locale_key.id = locale_key_message.key_id
                    WHERE
                        locale_key_message.locale = ?
                    AND
                        locale_key.namespace_id = ?
                ");
                $messages->execute([
                    $locale_iso2,
                    (int) $namespace_id,
                ]);

                $arr = [];
                foreach ($messages->fetchAll() as $message) {
                    $arr[crc32($message['k'])] = $message['v'];
                }

                return $arr;
            };

            return cache_lib::single(
                $config['cache_engine'],
                self::by_locale_namespace_key($locale_iso2, $namespace_id),
                $config['cache_pool'],
                $get
            );
        }

        /**
         * Delete cached translation dictionary
         *
         * @param string $locale_iso2
         * @param locale_namespace_model $namespace
         */
        public static function flush_by_locale_namespace($locale_iso2, locale_namespace_model $namespace) {

            $config = core::config()->system['locale'];

            cache_lib::delete(
                $config['cache_engine'],
                self::by_locale_namespace_key($locale_iso2, $namespace->id),
                $config['cache_pool']
            );
        }
    }