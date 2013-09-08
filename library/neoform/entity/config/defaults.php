<?php

    namespace neoform;

    /**
     * Default config object - extend this to define config values for an entity
     */
    abstract class entity_config_defaults {

        protected $config;

        /**
         * Constructor
         *
         * @param array $config
         */
        final public function __construct(array $config=[]) {
            $this->config = \array_replace_recursive($this->defaults(), $config);
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
         * @throws config_exception
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