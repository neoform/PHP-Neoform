<?php

    class locale_lib {

        public static function name($iso2) {
            return locale_dao::info($iso2)['name'];
        }

        public static function by_locale_namespace_key($locale, $namespace_id) {
            return 'locale_lib:by_locale_namespace:' . $locale . ($namespace_id ? ':' . $namespace_id : '');
        }

        public static function by_locale_namespace($locale, locale_namespace_model $namespace) {
            $get = function() use ($locale, $namespace) {
                $messages = core::sql('slave')->prepare("
                    SELECT
                        `locale_key`.`body` `k`,
                        `locale_key_message`.`body` `v`
                    FROM
                        `locale_key_message`
                    INNER JOIN
                        `locale_key`
                    ON
                        `locale_key`.`id` = `locale_key_message`.`key_id`
                    WHERE
                        `locale_key_message`.`locale` = ?
                    AND
                        `locale_key`.`namespace_id` = ?
                ");
                $messages->execute([
                    $locale,
                    $namespace->id,
                ]);

                $arr = [];
                foreach ($messages->fetchAll() as $message) {
                    $arr[crc32($message['k'])] = $message['v'];
                }

                return $arr;
            };

            return cache_lib::single(
                'memcache',
                self::by_locale_namespace_key($locale, $namespace->id),
                'entities',
                $get
            );
        }

        public static function flush_by_locale_namespace($locale, locale_namespace_model $namespace) {
            return cache_lib::delete(
                'memcache',
                self::by_locale_namespace_key($locale, $namespace->id),
                'entities'
            );
        }
    }