<?php

    namespace Neoform\Locale;

    use Neoform\Sql;
    use Neoform\Cache;
    use Neoform;

    class Lib {

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
        public static function byLocaleNamespace($locale_iso2, $namespace_id) {
            static $config;

            if (! $config) {
                $config = Neoform\Locale\Config::get();
            }

            $engine = $config->getCacheEngine();
            $engine = $engine::getService($config->getCacheEnginePoolRead())->get();

            $cacheKey = self::by_locale_namespace_key($locale_iso2, $namespace_id);

            if ($data = $engine->get($cacheKey)) {
                return $data;
            }

            $messages = Sql::getService(Neoform\Sql\Config::get()->getDefaultPoolRead())->get()->prepare("
                SELECT
                    locale_key.body k,
                    locale_key_message.body v
                FROM locale_key_message
                INNER JOIN locale_key ON locale_key.id = locale_key_message.key_id
                WHERE locale_key_message.locale = ?
                AND locale_key.namespace_id = ?
            ");
            $messages->execute([
                $locale_iso2,
                (int) $namespace_id,
            ]);

            $arr = [];
            foreach ($messages->fetchAll() as $message) {
                $arr[crc32($message['k'])] = $message['v'];
            }

            $engine = $config->getCacheEngine();
            $engine = $engine::getService($config->getCacheEnginePoolWrite())->get();
            $engine->set($cacheKey, $arr);

            return $arr;
        }

        /**
         * Delete cached translation dictionary
         *
         * @param string $locale_iso2
         * @param Neoform\Locale\Nspace\Model $namespace
         */
        public static function flushByLocaleNamespace($locale_iso2, Neoform\Locale\Nspace\Model $namespace) {

            $config = Neoform\Locale\Config::get();

            $engine = $config->getCacheEngine();
            $engine::getService($config->getCacheEnginePoolWrite())->get();
            $engine->delete(self::by_locale_namespace_key($locale_iso2, $namespace->id));
        }
    }
