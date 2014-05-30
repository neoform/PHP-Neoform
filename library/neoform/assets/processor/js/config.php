<?php

    namespace neoform\assets\processor\js;

    use neoform;

    class config extends neoform\config\model {

        /**
         * The array key this config file uses in the compiled configs
         *
         * @return string
         */
        public function get_array_key() {
            return 'assets\processor\css';
        }

        protected function defaults() {
            return [
                'search_replace' => [], // str_replace
                'patterns'       => [], // preg_replace or preg_replace_callback
            ];
        }

        /**
         * Validate the config values
         *
         * @throws neoform\config\exception
         */
        public function validate() {

            if (! is_array($this->config['search_replace'])) {
                throw new neoform\config\exception('"search_replace" must be an array');
            }

            if (! is_array($this->config['patterns'])) {
                throw new neoform\config\exception('"patterns" must be an array');
            }
        }

        /**
         * Validate the config values after the config has been compiled
         *
         * @throws neoform\config\exception
         */
        public function validate_post(array $config) {

        }
    }