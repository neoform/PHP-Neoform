<?php

    namespace Neoform;

    use DateTime;
    use Exception as PHPException;

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
     * PHP file extension
     */
    define('EXT', 'php');

    /**
     * Class Core - used to initialize the framework
     *
     * @package Neoform
     */
    class Core {

        /**
         * @var self
         */
        private static $instance;

        /**
         * @var string
         */
        private $docRootPath;

        /**
         * @var string
         */
        private $applicationPath;

        /**
         * @var string
         */
        private $libraryPath;

        /**
         * @var string
         */
        private $loggingPath;

        /**
         * @var string
         */
        private $cachePath;

        /**
         * The config environment object
         *
         * @var Config\Environment
         */
        private $environment;

        /**
         * Singleton - no copying allowed
         */
        final private function __clone() { }
        final private function __construct() { }

        /**
         * @param string $documentRoot
         * @param string $environmentClass
         * @param callable|null $autoloader
         *
         * @return Core
         * @throws PHPException
         */
        public static function build($documentRoot, $environmentClass, callable $autoloader=null) {
            if (self::$instance) {
                throw new PHPException('Neoform core has already been set up');
            }

            $documentRoot = realpath($documentRoot);

            if (! $documentRoot) {
                throw new PHPException('Document root is invalid');
            }

            self::$instance = $self = new self;

            $self->docRootPath     = $documentRoot;
            $self->applicationPath = "{$documentRoot}/application";
            $self->libraryPath     = "{$documentRoot}/library";
            $self->loggingPath     = "{$documentRoot}/logs";

            $self->loadIncludePaths();
            $self->loadAutoloader($autoloader);
            $self->setEnvironment(new $environmentClass);

            return $self;
        }

        /**
         * @return Core
         */
        public static function get() {
            return self::$instance;
        }

        /**
         * Application context (cli or web)
         *
         * @return string
         */
        public static function getContext() {
            return php_sapi_name() === 'cli' ? 'cli' : 'web';
        }

        /**
         * Environment object
         *
         * @return Config\Environment
         */
        public function getEnvironment() {
            return $this->environment;
        }

        /**
         * @return string
         */
        public function getDocRoot() {
            return $this->docRootPath;
        }

        /**
         * @return string
         */
        public function getApplicationPath() {
            return $this->applicationPath;
        }

        /**
         * @return string
         */
        public function getLibraryPath() {
            return $this->libraryPath;
        }

        /**
         * @return string
         */
        public function getLoggingPath() {
            return $this->loggingPath;
        }

        /**
         * @return string
         */
        public function getCachePath() {
            return $this->cachePath;
        }

        /**
         * Set the environment
         *
         * @param Config\Environment $environment
         *
         * @throws PHPException
         */
        private function setEnvironment(Config\Environment $environment) {

            if (! $this->applicationPath) {
                throw new PHPException('Application path must be set before environment');
            }

            if ($this->environment) {
                throw new PHPException('Environment has already been set');
            }

            $this->environment = $environment;

            // Figure out where the cache gets stored
            $this->cachePath = "{$this->applicationPath}/cache/{$environment->getName()}";

            // Tell the environment object where it can load/store cache
            $this->environment->setDao(new Config\Dao($this->cachePath));

            // Now that we have the environment object, we can load more settings
            $this->setErrorHandlers();

            ini_set('log_errors', 1);
            ini_set('error_log', "{$this->loggingPath}/errors.log");
            ini_set('log_errors_max_len', 0);
            ini_set('html_errors', 0);
            ini_set('display_errors', 'Off'); // do not display error(s) in browser - only affects non-fatal errors
            ini_set('display_startup_errors', 'Off');

            $config = Core\Config::get();

            mb_internal_encoding($config->getEncoding());
            date_default_timezone_set($config->GetTimezone());
        }

        /**
         * Initialize framework for use
         *
         * @throws PHPException
         */
        private function loadIncludePaths() {
            // Autoloader include path
            set_include_path(
                $this->applicationPath . // Application data/views
                PATH_SEPARATOR .
                $this->libraryPath // Library classes
            );
        }

        /**
         * Assign an autoloader, or use the default one
         *
         * @param callable|null $autoloader
         */
        private function loadAutoloader(callable $autoloader=null) {
            if (! $autoloader) {
                $autoloader = function($name) {
                    if (!include(str_replace([ '\\', '_' ], DIRECTORY_SEPARATOR, $name) . '.' . EXT)) {
                        throw new \Exception("Could not load file \"{$name}\"");
                    }
                };
            }

            spl_autoload_register($autoloader, true, true);
        }

        /**
         * Set default error/exception handlers
         */
        private function setErrorHandlers() {
            // Uncaught exception handler
            set_exception_handler(function(PHPException $e) {

                Error\Lib::log($e);

                if ((string) self::getContext() === 'web') {
                    self::show500();
                }

                if ($e instanceof \ErrorException) {
                    die("Uncaught Exception: {$e->getMessage()} - {$e->getFile()}:{$e->getLine()}\n");
                }

                die("Uncaught Exception: {$e->getMessage()}\n");
            });

            // PHP Error handler
            set_error_handler(function($err_number, $err_string, $err_file, $err_line) {

                // Error was suppressed with the @-operator
                if (! error_reporting()) {
                    return false;
                }

                throw new \ErrorException($err_string, $err_number, 0, $err_file, $err_line);
            });

            // Fatal error shutdown handler
            register_shutdown_function(function() {

                // Only grab error if there is one
                if (($error = error_get_last()) !== null) {

                    // This prevents obnoxious timezone warnings if the timezone has not been set
                    date_default_timezone_set(@date_default_timezone_get());

                    $message = isset($error['message']) ? $error['message'] : null;
                    $file    = isset($error['file']) ? $error['file'] : null;
                    $line    = isset($error['line']) ? $error['line'] : null;

                    Error\Lib::log(new PHPException("{$message} - {$file}:{$line}"));

                    if ((string) self::getContext() === 'web') {
                        self::show500();
                    }

                    die("FATAL ERROR [SHUTDOWN] - {$message} {$file} ({$line})\n");
                }
            });
        }

        /**
         * Save parameters to log file
         *
         * @param [mixed]
         *
         * @return null
         */
        public static function debug() {
            $args = func_get_args();
            self::log(count($args) === 1 ? current($args) : $args);
        }

        /**
         * Logs message to file, application dies if unable to log.
         *
         * @param mixed $msg
         * @param string $level (default: 'debug')
         * @param string $file  (default: 'errors')
         *
         * @return bool
         * @throws PHPException
         */
        public static function log($msg, $level='debug', $file='errors') {
            $logPath = self::$instance->loggingPath . '/';

            try {
                if (! file_exists($logPath)) {
                    mkdir($logPath, 0777, true);
                }

                if (is_writable($logPath)) {
                    $logPath .= preg_replace('`[^A-Z0-9_\-\.]`isx', '', $file) . '.log';

                    if (! is_string($msg)) {
                        $msg = print_r($msg, 1);
                    }

                    $now = new DateTime;

                    if ($level === 'debug') {
                        if ($backtrace = debug_backtrace()) {
                            if (isset($backtrace[1], $backtrace[1]['file'], $backtrace[1]['line'])) {
                                $msg = "File: {$backtrace[1]['file']}:{$backtrace[1]['line']}\n\n{$msg}";
                            }
                        }
                    }

                    if (isset($_SERVER['REMOTE_ADDR'], $_SERVER['REQUEST_URI'], $_SERVER['SERVER_NAME'], $_SERVER['HTTP_REFERER'])) {
                        $message = "{$now->format('Y-m-d H:i:s')} - " . strtoupper($level) . "\nIP: {$_SERVER['REMOTE_ADDR']} - URL: http://{$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']} - Referer: {$_SERVER['HTTP_REFERER']}\n{$msg}\n";
                    } else {
                        $message = "{$now->format('Y-m-d H:i:s')} - " . strtoupper($level) . "\n{$msg}\n";
                    }

                    if (file_put_contents($logPath, $message, FILE_APPEND) === false) {
                        throw new PHPException('Failed to write into a file...');
                    }

                    return true;
                }
            } catch (PHPException $e) {
                // This only happens when we have an error within this function.
            }

            echo "Could not write to: {$logPath}";
            die;
        }

        /**
         * Show a 500 error page according to configs
         */
        private static function show500() {
            $request = (new Request\Builder(Router\Config::get()))
                ->setServer([])
                ->setPath('/')
                ->build();

            $errorController       = Core\Config::get()->getDefaultErrorController();
            $errorControllerAction = Core\Config::get()->getDefaultErrorControllerAction();

            if ($errorController && $errorControllerAction) {
                $response   = new Response\Http\Builder;
                $controller = new $errorController($request, $response);
                $response->setView($controller->$errorControllerAction());
                $response->build()->render();
                die;
            }

            header('HTTP/1.1 500 Internal Server Error');
            echo "An unexpected error occurred\n";
            die;
        }
    }
