<?php

    namespace neoform\config;

    /**
     * Default config object - extend this to define config values for an entity
     */
    abstract class defaults {

        protected $config;

        /**
         * Constructor
         *
         * @param array $config
         */
        final public function __construct(array $config=[]) {
            $this->config = array_replace_recursive($this->defaults(), $config);
        }

        /**
         * Get the default config values
         *
         * @return array
         */
        abstract protected function defaults();

        /**
         * Validate the config values
         *
         * @throws exception
         */
        abstract public function validate();

        /**
         * Returns the config values as an array
         *
         * @return array
         */
        public function get_array() {
            return $this->config;
        }
    }