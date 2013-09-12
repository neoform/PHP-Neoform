<?php

    namespace neoform\locale;

    use neoform\core;
    use neoform\cache;

    class lib {

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
                $config = core::config()['locale'];
            }

            $get = function() use ($locale_iso2, $namespace_id) {
                $messages = core::sql(core::config()['sql']['default_pool_read'])->prepare("
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

            return cache\lib::single(
                $config['cache_engine'],
                $config['cache_engine_read'],
                $config['cache_engine_write'],
                self::by_locale_namespace_key($locale_iso2, $namespace_id),
                $get
            );
        }

        /**
         * Delete cached translation dictionary
         *
         * @param string $locale_iso2
         * @param \neoform\locale\npace\model $namespace
         */
        public static function flush_by_locale_namespace($locale_iso2, \neoform\locale\npace\model $namespace) {

            $config = core::config()['locale'];

            cache\lib::delete(
                $config['cache_engine'],
                $config['cache_engine_read'],
                $config['cache_engine_write'],
                self::by_locale_namespace_key($locale_iso2, $namespace->id)
            );
        }
    }
