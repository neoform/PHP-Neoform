<?php

    /**
     * Register the start time of the app
     */
    define('APP_START_TIME', microtime(1));

    /**
     * Turn on all error reporting
     */
    error_reporting(E_ALL);

    /**
     * Be literal with file permissions
     */
    umask(0);

    /**
    * Register autoloader
    */
    spl_autoload_register(
        function($name) {
            return include(str_replace(['\\', '_'], DIRECTORY_SEPARATOR, $name) . '.' . EXT);
        },
        true,
        true
    );

    /**
    * Core - first script loaded by main index.php file
    *        handles init of framework
    */
    class core {

        // Singletons
        protected static $instances = [];

        // Paths to directories used by framework
        protected static $paths = [];

        // What context was this application run (web/cli)
        protected static $environment;

        // Globals variables (use as sparingly as possible)
        protected static $globals = [];

        // Disable these methods
        final private function __clone() { }
        final private function __construct(array $args=null) {}

        /**
         * Singleton access
         *   eg: core::config('app'); core::sql('slave');
         *
         * @access public
         * @static
         * @param string|null $type
         * @param array $args
         * @return object instance
         */
        public static function __callstatic($type, array $args) {
            $name = count($args) === 1 ? (string) current($args) : '';
            if (! isset(self::$instances[$type][$name])) {
                $class = "{$type}_factory";
                self::$instances[$type][$name] = $class::init($args);
            }
            return self::$instances[$type][$name];
        }

        /**
         * Applications path
         *
         * @param $type
         *
         * @return string|null
         * @throws exception
         */
        public static function path($type) {
            if (isset(self::$paths[$type])) {
                return self::$paths[$type];
            }
            throw new exception("Path {$type} not set");
        }

        /**
         * Return the name of the environment
         *
         * @access public
         * @static
         * @return string
         */
        public static function environment() {
            return self::$environment;
        }

        /**
         * Application context
         *
         * @access public
         * @static
         * @return string
         */
        public static function context() {
            return php_sapi_name() === 'cli' ? 'cli' : 'web';
        }

        /**
         * Checks if a singleton is loaded or not
         *
         * @access public
         * @static
         * @param string $type
         * @param string|null $name (default: null)
         * @return boolean
         */
        public static function is_loaded($type, $name=null) {
            return isset(self::$instances[$type][$name]);
        }

        /**
         * Distroys a singleton
         *
         * @access public
         * @static
         * @param string $type
         * @param string|null $name (default: null)
         * @return boolean
         */
        public static function kill($type, $name=null) {
            if (isset(self::$instances[$type][$name])) {
                self::$instances[$type][$name]->kill();
                self::$instances[$type][$name] = null;
                return true;
            } else {
                return false;
            }
        }

        /**
         * Get global variable
         *
         * @access public
         * @static
         * @param string $k
         * @return mixed
         */
        public static function get($k) {
            if (isset(self::$globals[$k])) {
                return self::$globals[$k];
            }
        }

        /**
         * Set global variable
         *
         * @access public
         * @static
         * @param string $k
         * @param mixed $v
         * @return null
         */
        public static function set($k, $v) {
            self::$globals[$k] = $v;
        }

        /**
         * Initialize the framework
         *   sets paths, error handlers, default timezone, and a few constants
         *
         * @param array $params
         *
         * @throws ErrorException
         * @throws exception
         */
        public static function init(array $params) {

            if (isset($params['extension'])) {
                define('EXT', $params['extension']);
            } else {
                die("Config Error: PHP file extension not set. core::init([\"extension\" => [...] ]).\n");
            }

            // This file is always found in the library dir
            self::$paths['library'] = realpath(__DIR__);

            if (! isset($params['environment']) || ! self::$environment = $params['environment']) {
                die("Config Error: PHP file extension not set. core::init([\"environment\" => [...] [)\n");
            }

            if (! isset($params['application']) || ! self::$paths['application'] = realpath($params['application'])) {
                die("Config Error: PHP file extension not set or is invalid. core::init([\"application\" => [...] ])\n");
            }

            if (! isset($params['logs']) || ! self::$paths['logs'] = realpath($params['logs'])) {
                die("Config Error: Log dir path not set or is invalid. core::init([\"logs\" => [...] ])\n");
            }

            if (! isset($params['website']) || ! self::$paths['website'] = realpath($params['website'])) {
                die("Config Error: Web root path not set or is invalid. core::init([\"website\" => [...] ])\n");
            }

            if (! isset($params['external']) || ! self::$paths['external'] = realpath($params['external'])) {
                die("Config Error: External library path not set or is invalid. core::init([\"external\" => [...] ])\n");
            }

             //tell php where to find stuff
            set_include_path(
                self::$paths['application'] . '/library' . // core library overrides and entities
                PATH_SEPARATOR .
                self::$paths['library'] . // core library
                PATH_SEPARATOR .
                self::$paths['external'] // external libraries
            );
            define('WEB_ROOT', getcwd() . '/'); // Store the current dir as being the web root

            // Uncaught exception handler
            set_exception_handler(function(exception $e) {

                error_lib::log($e);

                switch ((string) core::context()) {
                    case 'web':
                        controller::error(500, null, null, true);
                        echo core::output()->send_headers()->body();
                        die;

                    default:
                        die("Error - " . $e->getMessage() . "\n");
                }
            });

            // PHP Error handler
            set_error_handler(function($err_number, $err_string, $err_file, $err_line) {

                // error was suppressed with the @-operator
                if (! error_reporting()) {
                    return false;
                }

                throw new ErrorException($err_string, $err_number, 0, $err_file, $err_line);
            });

            // Fatal error shutdown handler
            register_shutdown_function(function() {
                //only grab error if there is one
                if (($error = error_get_last()) !== null) {
                    //$type    = isset($error['type']) ? $error['type'] : null;
                    $message = isset($error['message']) ? $error['message'] : null;
                    $file    = isset($error['file']) ? $error['file'] : null;
                    $line    = isset($error['line']) ? $error['line'] : null;

                    core::log("{$message} {$file} ({$line})", 'fatal shutdown error');

                    switch ((string) core::context()) {
                        case 'web':
                            if (core::is_loaded('http')) {
                                try {
                                    controller::error(500, null, null, true);
                                } catch (Exception $e) {
                                    core::output()->body('Unexpected Error - There was a problem loading that page');
                                }

                                echo core::output()->send_headers()->body();
                            } else {
                                header('HTTP/1.1 500 Internal Server Error');
                                echo "An unexpected error occured\n";
                            }
                            die;

                        //case 'cli':
                        default:
                            die("FATAL ERROR - {$message} {$file} ({$line})\n");
                    }
                }
            });

            ini_set('log_errors', 1);
            ini_set('error_log', self::$paths['logs'] . '/errors.log');
            ini_set('log_errors_max_len', 0);
            ini_set('html_errors', 0);
            ini_set('display_errors', 'Off'); // do not display error(s) in browser - only affects non-fatal errors
            ini_set('display_startup_errors', 'Off');

            mb_internal_encoding(core::config()['core']['encoding']);
            date_default_timezone_set(core::config()['core']['timezone']);
        }

        /**
         * Save parameters to log file
         *
         * @access public
         * @static
         * @param [mixed]
         * @return null
         */
        public static function debug() {
            $args = func_get_args();
            core::log(count($args) === 1 ? current($args) : $args);
        }

        /**
         * Logs message to file, application dies if unable to log.
         *
         * @param mixed $msg
         * @param string $level (default: 'debug')
         * @param string $file  (default: 'errors')
         *
         * @return bool
         * @throws Exception
         */
        public static function log($msg, $level='debug', $file='errors') {
            try {
                $log_path = self::$paths['logs'] . '/';

                if (file_exists($log_path) && is_writable($log_path)) {
                    $file_name = preg_replace('`[^A-Z0-9_\-\.]`isx', '', $file) . '.log';
                    $log_path .= $file_name;

                    if (! is_string($msg)) {
                        $msg = print_r($msg, 1);
                    }

                    $dt = new datetime();

                    if (self::is_loaded('http')) {
                        $message = "\n" . $dt->format('Y-m-d H:i:s') . ' - ' . strtoupper($level) . "\n" . core::http()->server('ip') . ' /' . core::http()->server('query') . "\n{$msg}\n";
                    } else {
                        $message = "\n" . $dt->format('Y-m-d H:i:s') . ' - ' . strtoupper($level) . "\n" . "\n{$msg}\n";
                    }

                    if (file_put_contents($log_path, $message, FILE_APPEND) === false) {
                        throw new Exception('Failed to write into a file...');
                    }

                    return true;
                }
            } catch (Exception $e) {
                // This only happens when we have an error within this function.
            }

            echo "Could not write to: {$log_path}";
            die;
        }
    }

    /**
     * Compatibility with PHP 5.4
     */

    if (PHP_MAJOR_VERSION <= 5 && PHP_MINOR_VERSION < 5) {

        /**
         * This file is part of the array_column library
         *
         * For the full copyright and license information, please view the LICENSE
         * file that was distributed with this source code.
         *
         * @copyright Copyright (c) 2013 Ben Ramsey <http://benramsey.com>
         * @license http://opensource.org/licenses/MIT MIT
         */

        /**
         * Returns the values from a single column of the input array, identified by
         * the $columnKey.
         *
         * Optionally, you may provide an $indexKey to index the values in the returned
         * array by the values from the $indexKey column in the input array.
         *
         * @param array $input A multi-dimensional array (record set) from which to pull
         * a column of values.
         * @param mixed $columnKey The column of values to return. This value may be the
         * integer key of the column you wish to retrieve, or it
         * may be the string key name for an associative array.
         * @param mixed $indexKey (Optional.) The column to use as the index/keys for
         * the returned array. This value may be the integer key
         * of the column, or it may be the string key name.
         * @return array
         */
        function array_column($input = null, $columnKey = null, $indexKey = null) {

            $resultArray = array();

            foreach ($input as $row) {

                $key    = $value    = null;
                $keySet = $valueSet = false;

                if ($indexKey !== null && array_key_exists($indexKey, $row)) {
                    $keySet = true;
                    $key = (string) $row[$indexKey];
                }

                if ($columnKey === null) {
                    $valueSet = true;
                    $value = $row;
                } elseif (is_array($row) && array_key_exists($columnKey, $row)) {
                    $valueSet = true;
                    $value = $row[$columnKey];
                }

                if ($valueSet) {
                    if ($keySet) {
                        $resultArray[$key] = $value;
                    } else {
                        $resultArray[] = $value;
                    }
                }

            }

            return $resultArray;
        }
    }

