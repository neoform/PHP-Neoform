<?php

    namespace neoform\assets;

    use neoform;

    class config extends neoform\config\model {

        /**
         * The array key this config file uses in the compiled configs
         *
         * @return string
         */
        public function get_array_key() {
            return 'assets';
        }

        protected function defaults() {
            return [
                'enabled' => false,
                'types'   => [],
            ];
        }

        /**
         * Validate the config values
         *
         * @throws neoform\config\exception
         */
        public function validate() {

            // string name of neoform\assets\processor class

            if (! is_bool($this->config['enabled'])) {
                throw new neoform\config\exception('"enabled" must be boolean');
            }

            if ($this->config['enabled']) {

                if (! empty($this->config['types'])) {

                    if (! is_array($this->config['types'])) {
                        throw new neoform\config\exception('"types" must be an array');
                    }

                    foreach ($this->config['types'] as $type => $details) {

                        if (empty($details['path'])) {
                            throw new neoform\config\exception("['{$type}']['path'] must be set");
                        }

                        if (empty($details['url'])) {
                            throw new neoform\config\exception("['{$type}']['url'] must be set");
                        }

// This causes some kind of recursive error, disabled until I can figure out a solution. Maybe use secondary validate?
//                        if (! empty($details['processor'])) {
//                            if (class_exists($details['processor'])) {
//                                $processor = new $details['processor']([]);
//                                if (! ($processor instanceof processor)) {
//                                    throw new neoform\config\exception('"processor" must be an instance of neoform\assets\processor');
//                                }
//                            } else {
//                                throw new neoform\config\exception('"processor" must be an instance of neoform\assets\processor');
//                            }
//                        }
                    }
                }
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