<?php

    namespace Neoform\Router;

    use Neoform;

    class Config extends Neoform\Config\Model {

        /**
         * Site domain name
         *
         * @return string
         */
        public function getDomain() {
            return $this->values['domain'];
        }

        /**
         * Does a regular page use HTTPS
         *
         * @return bool
         */
        public function isHttpsRegular() {
            return (bool) $this->values['https']['regular'];
        }

        /**
         * Does a secure page use HTTPS
         *
         * @return bool
         */
        public function isHttpsSecure() {
            return (bool) $this->values['https']['secure'];
        }

        /**
         * Default Subdomain (pair)
         *
         * @return string[]
         */
        public function getSubdomainDefault() {
            return $this->values['subdomain_default'];
        }

        /**
         * Additional Subdomains (pairs)
         *
         * @return string[][]
         */
        public function getSubdomains() {
            return $this->values['subdomains'];
        }

        /**
         * @return string
         */
        public function getCdn() {
            return $this->values['cdn'];
        }

        /**
         * When a user does not have the required ACL permissions to access a given controller/action, fail silently
         * this means the user will not be aware that the controller exists, as opposed to seeing a '403: Access Denied'
         * page
         *
         * @return bool
         */
        public function isSilentAccessDenied() {
            return (bool) $this->values['silent_acccess_denied'];
        }

        /**
         * @return string
         */
        public function getRoutesMapClass() {
            return $this->values['routes_map_class'];
        }
    }