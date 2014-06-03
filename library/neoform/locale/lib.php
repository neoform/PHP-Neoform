<?php

    namespace neoform\locale;

    use neoform\sql;
    use neoform\cache;
    use neoform;

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
                $config = neoform\config::instance()['locale'];
            }

            $get = function() use ($locale_iso2, $namespace_id) {
                $messages = sql::instance(neoform\config::instance()['sql']['default_pool_read'])->prepare("
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
                true,
                self::by_locale_namespace_key($locale_iso2, $namespace_id),
                $get
            );
        }

        /**
         * Delete cached translation dictionary
         *
         * @param string $locale_iso2
         * @param \neoform\locale\nspace\model $namespace
         */
        public static function flush_by_locale_namespace($locale_iso2, \neoform\locale\nspace\model $namespace) {

            $config = neoform\config::instance()['locale'];

            cache\lib::delete(
                $config['cache_engine'],
                $config['cache_engine_read'],
                $config['cache_engine_write'],
                true,
                self::by_locale_namespace_key($locale_iso2, $namespace->id)
            );
        }
    }
