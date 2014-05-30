<?php

    namespace neoform\config;

    abstract class environment {

        /**
         * Compiled configs array
         *
         * @var array
         */
        protected $config        = [];

        /**
         * Array of config models
         *
         * @var array
         */
        protected $config_models = [];

        /**
         * Get configs as an array
         *
         * @return array
         */
        public function get_array() {
            return $this->config;
        }

        /**
         * Post compile validation
         *
         * @return $this
         */
        public function validate() {
            // Run post compile validation
            foreach ($this->config_models as $config) {
                $config->validate_post($this->config);
            }

            return $this;
        }

        /**
         * Append a config value along side the default value (this is not commonly used)
         *
         * @param model $config
         *
         * @return $this
         */
        protected function append_value(model $config) {
            $this->config          = array_merge_recursive($this->config, $config->get_array());
            $this->config_models[] = $config;

            return $this;
        }

        /**
         * Replace existing values within an array. (full arrays are not replaced, merely their scalar values)
         *
         * @param model $config
         *
         * @return $this
         */
        protected function merge(model $config) {
            $this->config          = array_replace_recursive($this->config, $config->get_array());
            $this->config_models[] = $config;

            return $this;
        }

        /**
         * Replace entire arrays of data in the config
         *
         * @param model $config
         *
         * @return $this
         */
        protected function crush(model $config) {
            $this->config          = array_replace($this->config, $config->get_array());
            $this->config_models[] = $config;

            return $this;
        }
    }