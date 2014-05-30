<?php

    namespace neoform\config;

    /**
     * Default config object - extend this to define config values for an entity
     */
    abstract class model {

        protected $config;

        /**
         * Constructor
         *
         * @param array $config
         */
        final public function __construct(array $config=[]) {

            // Merge the config data with the defaults
            $this->config = array_replace_recursive($this->defaults(), $config);

            // Validate the config data (first round of validation)
            $this->validate();
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
         * Validate the config values after the config has been compiled
         *
         * @throws exception
         */
        abstract public function validate_post(array $config);

        /**
         * The array key this config file uses in the compiled configs
         *
         * @return string
         */
        abstract public function get_array_key();

        /**
         * Returns the config values as an array
         *
         * @return array
         */
        final public function get_array() {
            return [
                $this->get_array_key() => $this->config,
            ];
        }
    }