<?php

    namespace Neoform\Cli;

    use Neoform;

    /**
     * Model intended to be extended when creating a console application
     */
    abstract class Model {

        /**
         * @var Neoform\Core
         */
        protected $core;

        /**
         * Array of opts sent from the console
         *
         * @var array
         * @access protected
         */
        protected $opts;

        /**
         * Array of CLI args
         *
         * @var array
         * @access protected
         */
        protected $argv;

        /**
         * Abstract initialization function
         *
         * @return void
         */
        abstract public function init();

        /**
         * 'ab:' means "-a and -b HELLO"
         *
         * @return string
         */
        protected function shortOptsDefintions() {
            return '';
        }

        /**
         * [ 'a', 'b:' ] means '--a --b=HELLO'
         *
         * @return array
         */
        protected function longOptsDefinitions() {
            return [];
        }

        /**
         * Model constructor.
         *
         * @param Neoform\Core $core
         * @param array $argv
         */
        final public function __construct(Neoform\Core $core, array $argv=[]) {
            if (! defined('STDIN')) {
                echo "This script must be run via CLI";
                exit(2);
            }

            $this->core = $core;
            $this->argv = $argv;

            // no funny business
            umask(0);

            $this->opts = getopt($this->shortOptsDefintions(), $this->longOptsDefinitions());

            $this->init();
        }

        /**
         * Get the value of an opt send from console
         *
         * @param string $k
         *
         * @return string|null
         */
        public function getOpt($k) {
            if (isset($this->opts[$k])) {
                return $this->opts[$k];
            }
        }

        /**
         * Is an opt set
         *
         * @param string $k
         *
         * @return boolean
         */
        public function optSet($k) {
            return array_key_exists($k, $this->opts);
        }

        /**
         * Get the value of an argv send from console
         *
         * @param int $k
         *
         * @return string|null
         */
        public function getArg($k) {
            if (isset($this->argv[$k])) {
                return $this->argv[$k];
            }
        }

        /**
         * Get an array of argv values
         *
         * @return string[]
         */
        public function getArgs() {
            return $this->argv;
        }

        /**
         * Get the current user running this script
         *
         * @return string
         */
        public static function getUser() {
            static $user;
            if (! $user) {
                $info = posix_getpwuid(posix_geteuid());
                $user = isset($info['name']) ? $info['name'] : 'Unknown';
            }
            return $user;
        }

        /**
         * Colorizes a string for output to a console
         *
         * @param string $str
         * @param string $color
         * @param bool $bold (default: false)
         * @param bool $reverse (default: false)
         *
         * @return string
         */
        public static function colorText($str, $color, $bold=false, $reverse=false) {
            if ($bold) {
                $x = 1;
            } elseif ($reverse) {
                $x = 7;
            } else {
                $x = 0;
            }

            switch ($color) {
                case 'black':   $y = 30; break;
                case 'blue':    $y = 34; break;
                case 'yellow':  $y = 33; break;
                case 'cyan':    $y = 36; break;
                case 'green':   $y = 32; break;
                case 'magenta': $y = 35; break;
                case 'red':     $y = 31; break;
                case 'white':   $y = 37; break;
                default:        $y = 0;
            }

            return "\033[{$x};{$y}m{$str}\033[0m";
        }

        /**
         * Executes command, exits with error code if failed
         *
         * @param $command
         *
         * @return array output of command
         */
        public static function run($command) {
            exec($command, $resp, $exit_code);

            if ((int) $exit_code !== 0) {
                echo self::colorText("FAILED: ", 'red', true);
                echo $command . "\n";
                exit($exit_code);
            }

            return $resp;
        }

        /**
         * Read line from console
         *
         * @return string
         */
        public static function readline() {
            return trim(fgets(STDIN));
        }

        /**
         * Get as password from console
         *
         * @return string
         */
        public static function readPassword() {
            system('stty -echo');
            $password = trim(fgets(STDIN));
            system('stty echo');
            // add a new line since the users CR didn't echo
            echo "\n";
            return $password;
        }
    }

