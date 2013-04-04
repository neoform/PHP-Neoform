<?php

    /**
     * Model intended to be extended when creating a console application
     */
    abstract class cli_model {

        /**
         * Array of opts sent from the console
         *
         * @var array
         * @access protected
         */
        protected $opts;

        /**
         * Abstract initialization function
         *
         * @access public
         * @abstract
         * @return void
         */
        abstract public function init();

        /**
         * @param string $shortopts
         * @param array  $longopts
         */
        final public function __construct($shortopts='', $longopts=[]) {
            if (! defined('STDIN')) {
                echo "This script must be run via CLI";
                exit(2);
            }

            // no funny business
            umask(0);

            $this->opts = getopt($shortopts, $longopts);

            $this->init();
        }

        /**
         * Get the value of an opt send from console
         *
         * @access public
         * @param string $k
         * @return void
         */
        public function opt($k) {
            if (isset($this->opts[$k])) {
                return $this->opts[$k];
            }
        }

        /**
         * Is an opt set
         *
         * @access public
         * @param string $k
         * @return boolean
         */
        public function opt_set($k) {
            return array_key_exists($k, $this->opts);
        }

        /**
         * Get the current user running this script
         *
         * @access public
         * @static
         * @return string
         */
        public static function get_user() {
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
         * @access public
         * @static
         * @param string $str
         * @param string $color
         * @param bool $bold (default: false)
         * @param bool $reverse (default: false)
         * @return string
         */
        public static function color_text($str, $color, $bold=false, $reverse=false) {
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

            return "\033[" . $x . ";" . $y . "m" . $str . "\033[0m";
        }

        /**
         * Executes command, exits with error code if failed
         *
         * @param $command
         * @return array output of command
         */
        public static function run($command) {
            exec($command, $resp, $exit_code);

            if ((int) $exit_code !== 0) {
                echo self::color_text("FAILED: ", 'red', true);
                echo $command . "\n";
                exit($exit_code);
            }

            return $resp;
        }

        /**
         * Read line from console
         *
         * @access public
         * @static
         * @return string
         */
        public static function readline() {
			return trim(fgets(STDIN));
		}
    }

