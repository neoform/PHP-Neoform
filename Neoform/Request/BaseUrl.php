<?php

    namespace Neoform\Request;

    use Neoform;

    /**
     * Class BaseUrl
     *
     * The base URL to use for links. It includes the protocol and the domain (no trailing slash).
     *
     * @package Neoform\Request
     */
    class BaseUrl {

        /**
         * @var Parameters\Server
         */
        protected $server;

        /**
         * @var Neoform\Http\Config
         */
        protected $config;

        /**
         * @var string
         */
        protected $regularBaseUrl;

        /**
         * @var string
         */
        protected $secureBaseUrl;

        /**
         * @var bool
         */
        protected $regularUrlIsHttps;

        /**
         * @var bool
         */
        protected $secureUrlIsHttps;

        /**
         * @var string
         */
        protected $regularDomain;

        /**
         * @var string
         */
        protected $secureDomain;

        /**
         * @var bool
         */
        protected $isValid = false;

        /**
         * @param Parameters\Server   $server
         * @param Neoform\Http\Config $config
         */
        public function __construct(Parameters\Server $server, Neoform\Http\Config $config) {
            $this->server = $server;
            $this->config = $config;

            $this->regularUrlIsHttps = (bool) $this->config->isHttpsRegular();
            $this->secureUrlIsHttps  = (bool) $this->config->isHttpsSecure();

            if (! $this->validateDomain()) {
                $this->applyDefaults();
            }

            // Assemble the regular/secure base URLs
            $this->regularBaseUrl = ($this->regularUrlIsHttps ? 'https://' : 'http://') . $this->regularDomain;
            $this->secureBaseUrl  = ($this->secureUrlIsHttps ? 'https://' : 'http://') . $this->secureDomain;
        }
        
        /**
         * Process Subdomains (validation)
         *
         * @return bool matched or not
         */
        protected function validateDomain() {

            // Domain root doesn't match, invalid
            if ($this->config->getDomain() !== $this->server->getDomainRoot()) {
                return false;
            }

            $subdomain = $this->server->getSubdomain();

            // Check if subdomain is valid (in the config)
            if ($subdomain && $this->config->getSubdomains()) {
                foreach ($this->config->getSubdomains() as $subdomainPair) {
                    if ($subdomainPair['regular'] === $subdomain || $subdomainPair['secure'] === $subdomain) {

                        $this->regularDomain = ($subdomainPair['regular'] ? "{$subdomainPair['regular']}." : '' )
                            . $this->config->getDomain();

                        $this->secureDomain = ($subdomainPair['secure'] ? "{$subdomainPair['secure']}." : '')
                            . $this->config->getDomain();

                        $this->validateProtocol($subdomainPair);

                        return true;
                    }
                }
            }

            return false;
        }

        /**
         * A domain can only be valid if we're using the correct protocol with it
         *
         * @param string[] $subdomainPair
         */
        private function validateProtocol(array $subdomainPair) {

            // Make sure we're using the correct protocol
            if (! $this->isValidRegular() && $subdomainPair['regular'] === $this->server->getSubdomain()) {
                $this->isValid = true;
                return;
            }

            if ($this->isValidSecure() && $subdomainPair['secure'] === $this->server->getSubdomain()) {
                $this->isValid = true;
                return;
            }
        }

        /**
         * If domain does not match any valid domain configations, apply defaults
         */
        protected function applyDefaults() {

            $subdomainPair = $this->config->getSubdomainDefault();

            $this->regularDomain = ($subdomainPair['regular'] ? "{$subdomainPair['regular']}." : '' ) . $this->config->getDomain();
            $this->secureDomain  = ($subdomainPair['secure'] ? "{$subdomainPair['secure']}." : '') . $this->config->getDomain();
        }

        /**
         * Does the domain/protocol of the Request model match the requirements of the configs
         *
         * @return bool
         */
        public function isValid() {
            return $this->isValid;
        }

        /**
         * Does this conform with the HTTPs requirements for a "regular" base URL?
         *
         * @return bool
         */
        public function isValidRegular() {
            if ($this->config->isHttpsRegular()) {
                return $this->server->isHttps();
            } else {
                return ! $this->server->isHttps();
            }
        }

        /**
         * Does this conform with the HTTPs requirements for a "secure" base URL?
         *
         * @return bool
         */
        public function isValidSecure() {
            if ($this->config->isHttpsSecure()) {
                return $this->server->isHttps();
            } else {
                return ! $this->server->isHttps();
            }
        }

        /**
         * Regular URL
         *
         * @return string
         */
        public function getRegularBaseUrl() {
            return $this->regularBaseUrl;
        }

        /**
         * Secure URL
         *
         * @return string
         */
        public function getSecureBaseUrl() {
            return $this->secureBaseUrl;
        }

        /**
         * Is the regular URL https
         *
         * @return bool
         */
        public function isRegularUrlHttps() {
            return $this->regularUrlIsHttps;
        }

        /**
         * Is the secure URL https
         *
         * @return bool
         */
        public function isSecureUrlHttps() {
            return $this->secureUrlIsHttps;
        }

        /**
         * Regular domain
         *
         * @return string
         */
        public function getRegularDomain() {
            return $this->regularDomain;
        }

        /**
         * Secure domain
         *
         * @return string
         */
        public function getSecureDomain() {
            return $this->secureDomain;
        }
    }
