<?php

    namespace Neoform\Router\Config;

    use Neoform;

    class Builder extends Neoform\Config\Builder {

        /**
         * @return Neoform\Redis\Config
         */
        public function build() {
            return new Neoform\Router\Config($this->configValues);
        }

        /**
         * Config default values
         *
         * @return array
         */
        protected function defaults() {
            return [
                'routes_map_class' => null,
            ];
        }

        /**
         * Validate the config values
         *
         * @throws Neoform\Config\Exception
         */
        public function validate() {
            if (empty($this->configValues['routes_map_class'])) {
                throw new Neoform\Config\Exception('"routes_map_class" must be set');
            }

            if (! is_subclass_of($this->configValues['routes_map_class'], 'Neoform\Http\Routes')) {
                throw new Neoform\Config\Exception('"routes_map_class" must be an instance of Neoform\Http\Routes');
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