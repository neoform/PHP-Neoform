<?php

    namespace Neoform\Web\Config;

    use Neoform;

    class Builder extends Neoform\Config\Builder {

        /**
         * @return Neoform\Web\Config
         */
        public function build() {
            return new Neoform\Web\Config($this->configValues);
        }

        /**
         * Config default values
         *
         * @return array
         */
        protected function defaults() {
            return [
                // User agent
                'user_agent' => null,
            ];
        }

        /**
         * Validate the config values
         *
         * @throws Neoform\Config\Exception
         */
        public function validate() {

            if (empty($this->configValues['user_agent'])) {
                throw new Neoform\Config\Exception('"user_agent" must be set');
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