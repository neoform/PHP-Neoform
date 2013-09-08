<?php

    namespace neoform;

    abstract class config_environment {

        protected $config = [];

        /**
         * Get configs as an array
         *
         * @return array
         */
        public function to_array() {
            $arr = [];
            foreach ($this->config as $k => $config) {
                if ($config instanceof entity_config_defaults) {
                    $config->validate();
                    $arr[$k] = $config->get_array();
                }
            }
            return $arr;
        }

        /**
         * Append a config value along side the default value (this is not commonly used)
         *
         * @param array $config
         */
        protected function append_value(array $config) {
            $this->config = \array_merge_recursive($this->config, $config);
        }

        /**
         * Replace existing values within an array. (full arrays are not replaced, merely their scalar values)
         *
         * @param array $config
         */
        protected function merge(array $config) {
            $this->config = \array_replace_recursive($this->config, $config);
        }

        /**
         * Replace entire arrays of data in the config
         *
         * @param array $config
         */
        protected function crush(array $config) {
            $this->config = \array_replace($this->config, $config);
        }
    }