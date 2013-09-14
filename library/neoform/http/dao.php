<?php

    namespace neoform\http;

    use neoform;

    class dao {

        /**
         * Relative path of routes config cache
         */
        const ROUTES_CACHE_DIR_PATH = '/cache/routes/';

        /**
         * Get current routing information from cache, if cache doesn't exist, generate it.
         *
         * @param $locale
         *
         * @return array|mixed|null
         * @throws \exception
         */
        public static function get($locale) {

            try {
                $info = include(neoform\core::path('application') . self::ROUTES_CACHE_DIR_PATH . "{$locale}." . EXT);
            } catch (\exception $e) {
                $info = null;
            }

            if (! isset($info['routes']) || ! isset($info['controllers'])) {
                return self::create($locale);
            }

            return $info;
        }

        /**
         * Parse and save route config information to cache
         *
         * @param string $locale
         *
         * @return array|null
         * @throws exception
         */
        public static function create($locale) {

            $routes = routes::get();

            if (! $routes instanceof route) {
                throw new exception("Routes file must contain at least one route");
            }

            $return = null;

            foreach (neoform\core::config()['locale']['allowed'] as $file_locale) {
                $info = [
                    'controllers'   => [],
                    'routes'        => [],
                ];

                $locale_routes = $routes->_routes($file_locale, $routes);

                //remove useless routes
                foreach ($locale_routes as $k => $v) {
                    if ($k !== $v) {
                        $info['routes'][$k] = $v;
                    }
                }

                $info['controllers'] = $routes->_controllers($file_locale, $routes);

                $code = '<'.'?'.'php'.
                        "\n\n// DO NOT MODIFY THIS FILE DIRECTLY, IT IS A CACHE FILE AND GETS OVERWRITTEN AUTOMATICALLY.\n\n".
                        'return ' . var_export($info, true) . ";";

                if (! neoform\disk\lib::file_put_contents(neoform\core::path('application') . self::ROUTES_CACHE_DIR_PATH . "{$file_locale}." . EXT, $code)) {
                    throw new exception('Could not save the parsed controller map to the cache directory: ' . neoform\core::path('application') . self::ROUTES_CACHE_DIR_PATH . "{$file_locale}." . EXT);
                }

                if ($locale === $file_locale) {
                    $return = $info;
                }
            }

            return $return;
        }

        /**
         * Delete route config cache
         *
         * @param $locale
         */
        public static function del($locale) {
            unlink(neoform\core::path('application') . self::ROUTES_CACHE_DIR_PATH . "{$locale}." . EXT);
        }
    }