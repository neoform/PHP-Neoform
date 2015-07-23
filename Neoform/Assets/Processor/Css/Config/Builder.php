<?php

    namespace Neoform\Assets\Processor\Css\Config;

    use Neoform;

    class Builder extends Neoform\Config\Builder {

        /**
         * @return Neoform\Assets\Processor\Css\Config
         */
        public function build() {
            return new Neoform\Assets\Processor\Css\Config($this->configValues);
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
         * @throws Neoform\Config\Exception
         */
        public function validate() {

            if (! is_array($this->configValues['search_replace'])) {
                throw new Neoform\Config\Exception('"search_replace" must be an array');
            }

            if (! is_array($this->configValues['patterns'])) {
                throw new Neoform\Config\Exception('"patterns" must be an array');
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