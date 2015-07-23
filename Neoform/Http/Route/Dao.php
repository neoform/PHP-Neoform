<?php

    namespace Neoform\Http\Route;

    use Neoform\Core;
    use Neoform\Http;
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
         * @param string $locale
         *
         * @return string
         */
        protected function compiledFilePath($locale) {
            $file = str_replace('\\', '/', $locale);
            return "{$this->cachePath}/routes/{$file}." . EXT;
        }

        /**
         * Get current routing information from cache, if cache doesn't exist, generate it.
         *
         * @param string $locale
         *
         * @return array|mixed|null
         * @throws \Exception
         */
        public function get($locale) {

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
         * @param string $locale
         *
         * @return array|null
         * @throws Http\Exception
         */
        public function create($locale) {

            $routesMapClass = $this->config->getRoutesMapClass();
            $routes = (new $routesMapClass)->get();

            if (! $routes instanceof Http\Route\Model) {
                throw new Http\Exception("Routes file must contain at least one route");
            }

            $return = null;

            foreach (Neoform\Locale\Config::get()->getAllowed() as $fileLocale) {
                $info = [
                    'controllers'   => [],
                    'routes'        => [],
                ];

                $localeRoutes = $routes->_routes($fileLocale, $routes);

                //remove useless routes
                foreach ($localeRoutes as $k => $v) {
                    if ($k !== $v) {
                        $info['routes'][$k] = $v;
                    }
                }

                $info['controllers'] = $routes->_controllers($fileLocale);

                $code = '<'.'?'.'php'.
                        "\n\n// DO NOT MODIFY THIS FILE DIRECTLY, IT IS A CACHE FILE AND GETS OVERWRITTEN AUTOMATICALLY.\n\n".
                        'return ' . var_export($info, true) . ";";

                if (! Disk\Lib::file_put_contents($this->compiledFilePath($locale), $code)) {
                    throw new Http\Exception('Could not save the parsed controller map to the cache directory: ' . $this->compiledFilePath($locale));
                }

                if ($locale === $fileLocale) {
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
        public function del($locale) {
            unlink($this->compiledFilePath($locale));
        }
    }