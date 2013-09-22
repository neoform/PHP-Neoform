<?php

    namespace neoform\config;

    use neoform;

    /**
     *  Config DAO
     */
    class dao {

        /**
         * Directory where config cache files are stored
         */
        const CONF_CACHE_DIR = '/cache/config';

        /**
         * Directory where config files are stored
         */
        const CONF_INI_DIR = '/config';

        /**
         * Load a config
         *
         * @param string $file
         *
         * @return array
         */
        public static function get($file) {
            try {
                return include(neoform\core::path('application') . self::CONF_CACHE_DIR . '/' . neoform\core::environment() . ($file ? "/{$file}." : '.') . EXT);
            } catch (\exception $e) {
                return self::set($file);
            }
        }

        /**
         * Load config from source and compile into cache file
         *
         * @param string      $file
         * @param string|null $environment
         *
         * @return array
         * @throws exception
         * @throws \exception
         */
        public static function set($file, $environment=null) {

            $config_class = '\\neoform\\' . neoform\core::environment() . ($file ? '_' . str_replace('/', '_', $file) : '');

            $config = (new $config_class)->to_array();

            $code = '<'.'?'.'php'.
                    "\n\n// DO NOT MODIFY THIS FILE DIRECTLY, IT IS A CACHE FILE AND GETS OVERWRITTEN AUTOMATICALLY.\n\n".
                    'return ' . var_export($config, true) . ";\n\n";

            if (! neoform\disk\lib::file_put_contents(neoform\core::path('application') . self::CONF_CACHE_DIR . '/' . ($environment ?: neoform\core::environment()) . ($file ? "/{$file}." : '.') . EXT, $code)) {
                throw new \exception('Could not write to the config cache file.');
            }

            return $config;
        }

        /**
         * Delete a config cache file
         *
         * @param string $file
         */
        public static function del($file) {
            unlink(neoform\core::path('application') . self::CONF_CACHE_DIR . '/' . neoform\core::environment() . ($file ? "/{$file}." : '.') . EXT);
        }
    }