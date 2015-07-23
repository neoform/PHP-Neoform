<?php

    namespace Neoform\Assets\Config;

    use Neoform;

    class Builder extends Neoform\Config\Builder {

        /**
         * @return Neoform\Assets\Config
         */
        public function build() {
            return new Neoform\Assets\Config($this->configValues);
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
         * @throws Neoform\Config\Exception
         */
        public function validate() {

            // string name of Neoform\Assets\Processor class

            if (! is_bool($this->configValues['enabled'])) {
                throw new Neoform\Config\Exception('"enabled" must be boolean');
            }

            if ($this->configValues['enabled']) {

                if (! empty($this->configValues['types'])) {

                    if (! is_array($this->configValues['types'])) {
                        throw new Neoform\Config\Exception('"types" must be an array');
                    }

                    foreach ($this->configValues['types'] as $type => $details) {

                        if (empty($details['path'])) {
                            throw new Neoform\Config\Exception("['{$type}']['path'] must be set");
                        }

                        if (empty($details['url'])) {
                            throw new Neoform\Config\Exception("['{$type}']['url'] must be set");
                        }

                        if (! empty($details['processor'])) {

                            if (! class_exists($details['processor'])) {
                                throw new Neoform\Config\Exception(
                                    '"processor" must be an instance of Neoform\Assets\Processor'
                                );
                            }

                            if (! is_subclass_of($details['processor'], 'Neoform\Assets\Processor')) {
                                throw new Neoform\Config\Exception(
                                    "\"{$details['processor']}\" invalid - \"processor\" must be an instance of Neoform\\Assets\\processor"
                                );
                            }
                        }
                    }
                }
            }
        }

        /**
         * Validate the config values after the config has been compiled
         *
         * @param Neoform\Config\Collection $configs
         */
        public function validatePost(Neoform\Config\Collection $configs) {

        }
    }