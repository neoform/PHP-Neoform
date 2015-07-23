<?php

    namespace Neoform\Http\Config;

    use Neoform;

    class Builder extends Neoform\Config\Builder {

        /**
         * @return Neoform\Http\Config
         */
        public function build() {
            return new Neoform\Http\Config($this->configValues);
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

                'cdn' => null,
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

            if (empty($this->configValues['cdn'])) {
                throw new Neoform\Config\Exception('$this->config[\'cdn\'] must be set');
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