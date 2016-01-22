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
                // Site domain name [required]
                'domain' => null,

                // https settings [required]
                'https' => [
                    'regular' => false,
                    'secure'  => false,
                ],

                // default subdomain [required]
                'subdomain_default' => [
                    'regular' => null,
                    'secure'  => null,
                ],

                // additional subdomain pairs [optional]
                'subdomains' => [],

                // When a user does not have the required ACL permissions to access a given controller/action, fail silently
                // this means the user will not be aware that the controller exists, as opposed to seeing a '403: Access Denied'
                // page
                'silent_acccess_denied' => true,

                // CDN base URL
                'cdn' => null,

                // Neoform\Router\Routes implementation
                'routes_map_class' => null,
            ];
        }

        /**
         * Validate the config values
         *
         * @throws Neoform\Config\Exception
         */
        public function validate() {

            if (empty($this->configValues['domain'])) {
                throw new Neoform\Config\Exception('"domain" must be set');
            }

            if (empty($this->configValues['routes_map_class'])) {
                throw new Neoform\Config\Exception('"routes_map_class" must be set');
            }

            if (! is_subclass_of($this->configValues['routes_map_class'], 'Neoform\Router\Routes')) {
                throw new Neoform\Config\Exception('"routes_map_class" must be an instance of Neoform\Router\Routes');
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