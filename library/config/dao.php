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
			//constantly checking if conf exists isn't that great...  but......
			$filepath = core::path('application') . self::CONF_CACHE_DIR . '/' . core::environment() . $file . '.' . EXT;
			if (file_exists($filepath)) {
				//load the config file (info $config - as a global)
				$config = require($filepath);

				//check if the config.ini has been updated
				if (! isset($config['config']) || ! isset($config['filectime']) || filectime(core::path('application') . self::CONF_INI_DIR . '/' . core::environment() . $file . '.json') !== $config['filectime']) {
					return self::set($file);
				} else {
					return $config['config'];
				}
			} else {
				return self::set($file);
			}
		}

        /**
         * Load config from source and compile into cache file
         *
         * @param string $file
         *
         * @return array
         * @throws config_exception
         * @throws Exception
         */
        public static function set($file) {

			$path = core::path('application') . self::CONF_INI_DIR . '/' . core::environment() . $file . '.json';

			if (! file_exists($path) || ! is_readable($path)) {
				throw new Exception('Configuration file could not be read: ' . $path);
			}

			$json = file_get_contents($path);

			$json = trim(preg_replace('`^\s*//(.[^\n]*?)?\s*$`m', "\n", $json));

            $config = json_decode($json, true);

			if (! $config || ! is_array($config) || ! count($config)) {
				throw new config_exception("Could not parse config file " . $path . ' - ' . type_string_json::last_error());
			}

            $info = [
                'config'    => $config,
                'filectime' => filectime($path),
            ];

			$code = '<'.'?'.'php'.
					"\n\n// DO NOT MODIFY THIS FILE DIRECTLY, IT IS A CACHE FILE AND GETS OVERWRITTEN AUTOMATICALLY.\n\n".
					'return ' . var_export($info, true) . ";\n\n";

			if (! disk_lib::file_put_contents(core::path('application') . self::CONF_CACHE_DIR . '/' . core::environment() . $file . '.' . EXT, $code)) {
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
			unlink(core::path('application') . self::CONF_CACHE_DIR . '/' . core::environment() . $file . '.' . EXT);
		}
	}