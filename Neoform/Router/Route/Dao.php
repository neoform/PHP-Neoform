<?php

    namespace Neoform\Router\Route;

    use Neoform\Core;
    use Neoform\Router\Exception;
    use Neoform\Disk;
    use Neoform;

    class Dao {

        /**
         * @var string
         */
        protected $cachePath;

        /**
         * @var Neoform\Router\Config
         */
        protected $config;

        /**
         * @param string $cachePath
         */
        public function __construct($cachePath, Neoform\Router\Config $config) {
            $this->cachePath = $cachePath;
            $this->config    = $config;
        }

        /**
         * The file path where the compiled routes are stored
         *
         * @param string|null $locale
         *
         * @return string
         */
        protected function compiledFilePath($locale=null) {
            if (! $locale) {
                $locale = 'default';
            }
            return "{$this->cachePath}/routes/{$locale}." . EXT;
        }

        /**
         * Get current routing information from cache, if cache doesn't exist, generate it.
         *
         * @param string|null $locale
         *
         * @return array|mixed|null
         * @throws \Exception
         */
        public function get($locale=null) {

            try {
                $info = include($this->compiledFilePath($locale));
            } catch (\Exception $e) {
                $info = null;
            }

            if (! isset($info['routes']) || ! isset($info['controllers'])) {
                return $this->create($locale);
            }

            return $info;
        }

        /**
         * Parse and save route config information to cache
         *
         * @param string|null $locale
         *
         * @return array|null
         * @throws Exception
         */
        public function create($locale=null) {

            $routesMapClass = $this->config->getRoutesMapClass();
            $routes = (new $routesMapClass)->get();

            if (! $routes instanceof Model) {
                throw new Exception("Routes file must contain at least one route");
            }

            $return = $this->generateRouteCache($routes);

            if (! $locale) {
                return $return;
            }

            foreach (Neoform\Locale\Config::get()->getAllowed() as $allowedLocale) {
                $info = $this->generateRouteCache($routes, $allowedLocale);

                if ($locale === $allowedLocale) {
                    $return = $info;
                }
            }

            return $return;
        }

        /**
         * Delete route config cache
         *
         * @param $locale|null
         */
        public function del($locale=null) {
            unlink($this->compiledFilePath($locale));
        }

        /**
         * @param Model       $routes
         * @param string|null $locale
         *
         * @return array
         * @throws Exception
         */
        protected function generateRouteCache(Model $routes, $locale=null) {
            $info = [
                'controllers' => $routes->_controllers($locale),
                'routes'      => [],
            ];

            if ($locale) {
                $localeRoutes = $routes->_routes($routes, $locale);

                // Remove useless routes
                foreach ($localeRoutes as $k => $v) {
                    if ($k !== $v) {
                        $info['routes'][$k] = $v;
                    }
                }
            }

            $code = '<?php'.
                "\n\n// DO NOT MODIFY THIS FILE DIRECTLY, IT IS A CACHE FILE AND GETS OVERWRITTEN AUTOMATICALLY.\n\n".
                'return ' . var_export($info, true) . ";";

            if (! Disk\Lib::file_put_contents($this->compiledFilePath($locale), $code)) {
                throw new Exception("Could not save the parsed controller map to the cache directory: {$this->compiledFilePath($locale)}");
            }

            return $info;
        }
    }