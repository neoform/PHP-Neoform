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
        protected function append(model $config) {
            $key = $config->get_array_key();

            // If a previous config exists, merge new data into it
            if (isset($this->config_models[$key])) {
                $this->config_models[$key]->append($config);
                $this->config[$key] = $this->config_models[$key]->get_array();
            } else {
                $this->config[$key]        = $config->get_array();
                $this->config_models[$key] = $config;
            }

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
            $key = $config->get_array_key();

            // If a previous config exists, merge new data into it
            if (isset($this->config_models[$key])) {
                $this->config_models[$key]->merge($config);
                $this->config[$key] = $this->config_models[$key]->get_array();
            } else {
                $this->config[$key]        = $config->get_array();
                $this->config_models[$key] = $config;
            }

            return $this;
        }

        /**
         * Replace using array_replace on the values in the config
         *
         * @param model $config
         *
         * @return $this
         */
        protected function crush(model $config) {
            $key = $config->get_array_key();

            // Crush previous values in a config
            if (isset($this->config_models[$key])) {
                $this->config[$key] = array_replace($this->config[$key], $config->get_array());
            } else {
                $this->config_models[$key] = $config;
            }

            return $this;
        }

        /**
         * Replace all values in this config
         *
         * @param model $config
         */
        protected function replace(model $config) {
            $key = $config->get_array_key();

            // Crush all previous config values by that key
            $this->config[$key]        = $config->get_array();
            $this->config_models[$key] = $config;
        }
    }