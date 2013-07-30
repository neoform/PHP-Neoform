<?php

    abstract class config_defaults {

        protected $config;

        /**
         * Constructor
         *
         * @param array $config
         */
        final public function __construct(array $config=[]) {
            $this->config = array_replace_recursive($this->defaults(), $config);
        }

        abstract protected function defaults();

        public function validate() {

        }

        public function get_array() {
            return $this->config;
        }
    }