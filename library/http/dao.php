<?php

    class http_dao {

        /**
         * Relative path of routes config file
         */
        const ROUTES_FILE_PATH = '/config/routes.php';

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
         */
        public static function get($locale) {

            //if the controller map cache does not exist, generate it
            if (file_exists(core::path('application') . self::ROUTES_CACHE_DIR_PATH . $locale . '.' . EXT)) {

                $info = require(core::path('application') . self::ROUTES_CACHE_DIR_PATH . $locale . '.' . EXT);

                if (! isset($info['routes']) || ! isset($info['controllers']) || filectime(core::path('application') . self::ROUTES_FILE_PATH) !== $info['last_modified']) {
                    return self::create($locale);
                }

                return $info;

            //file doesn't exist - reload
            } else {
                return self::create($locale);
            }
        }

        /**
         * Parse and save route config information to cache
         *
         * @param string $locale
         *
         * @return array|null
         * @throws http_exception
         */
        public static function create($locale) {

            $path = core::path('application') . self::ROUTES_FILE_PATH;

            if (! is_readable($path) || ! file_exists($path)) {
                throw new http_exception('Routes file could not be read: ' . $path);
            }

            $routes = require($path);

            if (! $routes instanceof http_route) {
                throw new http_exception("Routes file contains no routes: " . $path);
            }

            $return = null;

            foreach (core::config()->system['locale']['allowed'] as $file_locale) {
                $info = [
                    'last_modified' => filectime($path),
                    'controllers'    => [],
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

                if (! disk_lib::file_put_contents(core::path('application') . self::ROUTES_CACHE_DIR_PATH . $file_locale . '.' . EXT, $code)) {
                    throw new http_exception('Could not save the parsed controller map to the cache directory: ' . core::path('application') . self::ROUTES_CACHE_DIR_PATH . $file_locale . '.' . EXT);
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
            unlink(core::path('application') . self::ROUTES_CACHE_DIR_PATH . $locale . '.' . EXT);
        }
    }