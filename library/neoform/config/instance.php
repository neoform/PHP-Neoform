<?php

    namespace neoform\config;

    /**
     * Instance of a config
     */
    class instance extends \ArrayObject {

        use \neoform\core\instance;

        protected $vars;

        public function __construct($file=null) {
            $this->exchangeArray(
                dao::get($file)
            );
        }

        public function __get($key) {
            return isset($this[$key]) ? $this[$key] : null;
        }

        public function overload(array $overload) {
            $this->exchangeArray(
                array_replace_recursive((array) $this, $overload)
            );
        }
    }