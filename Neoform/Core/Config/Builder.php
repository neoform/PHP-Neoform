<?php

    namespace Neoform\Core\Config;

    use Neoform;

    class Builder extends Neoform\Config\Builder {

        /**
         * @return Neoform\Core\Config
         */
        public function build() {
            return new Neoform\Core\Config($this->configValues);
        }

        /**
         * Config default values
         *
         * @return array
         */
        protected function defaults() {
            return [
                'site_name' => null,

                // When using multiple sites with a common db, user accounts are created under this site_id
                'site_id' => 1,

                // manually set the timezone [required]
                'timezone' => 'UTC',

                // page output encoding [required]
                'encoding' => 'utf-8',

                // Controller that handles uncaught errors/exceptions by default
                'default_error_controller' => null,

                // Controller action that handles uncaught errors/exceptions by default
                'default_error_controller_action' => null,
            ];
        }

        /**
         * Validate the config values
         */
        public function validate() {
            if ((int) $this->configValues['site_id'] != $this->configValues['site_id'] || $this->configValues['site_id'] < 1) {
                throw new Neoform\Config\Exception('["site_id"] must be an unsigned integer');
            }

            if ($this->configValues['default_error_controller'] !== null
                && ! is_subclass_of($this->configValues['default_error_controller'], 'Neoform\Router\Controller')) {
                throw new Neoform\Config\Exception('["default_error_controller"] must be an instance of Neoform\Router\Controller');
            }

            if ($this->configValues['default_error_controller_action'] !== null
                && ! method_exists($this->configValues['default_error_controller'], $this->configValues['default_error_controller_action'])) {
                throw new Neoform\Config\Exception('["default_error_controller_action"] must be an method of ' . $this->configValues['default_error_controller']);
            }
        }

        /**
         * Validate the config values after the config has been compiled
         */
        public function validatePost(Neoform\Config\Collection $configs) {

        }
    }