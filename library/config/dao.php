<?php

    /**
     *  Config DAO
     */
    class config_dao {

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
                $config = include(core::path('application') . self::CONF_CACHE_DIR . '/' . core::environment() . ($file ? "/{$file}." : '.') . EXT);
            } catch (exception $e) {
                $config = null;
            }

            return $config ?: self::set($file);
        }

        /**
         * Load config from source and compile into cache file
         *
         * @param string      $file
         * @param string|null $environment
         *
         * @return array
         * @throws config_exception
         * @throws Exception
         */
        public static function set($file, $environment=null) {

            $config_class = core::environment() . ($file ? '_' . str_replace('/', '_', $file) : '');

            $config = (new $config_class)->to_array();

            $code = '<'.'?'.'php'.
                    "\n\n// DO NOT MODIFY THIS FILE DIRECTLY, IT IS A CACHE FILE AND GETS OVERWRITTEN AUTOMATICALLY.\n\n".
                    'return ' . var_export($config, true) . ";\n\n";

            if (! disk_lib::file_put_contents(core::path('application') . self::CONF_CACHE_DIR . '/' . ($environment ?: core::environment()) . ($file ? "/{$file}." : '.') . EXT, $code)) {
                throw new Exception('Could not write to the config cache file.');
            }

            return $config;
        }

        /**
         * Delete a config cache file
         *
         * @param string $file
         */
        public static function del($file) {
            unlink(core::path('application') . self::CONF_CACHE_DIR . '/' . core::environment() . ($file ? "/{$file}." : '.') . EXT);
        }
    }