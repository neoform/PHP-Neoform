<?php

    namespace neoform;

    class http_config extends entity_config_defaults {

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

                // Cookie related configs
                'cookies' => [
                    // 4 years in seconds [required]
                    'ttl' => 126227704,

                    // cookie path [optional]
                    //'path' => '/',

                    // only allow cookies to be read via https [required]
                    'secure' => false,

                    // only allow cookies to be read by http and no javascript
                    'httponly' => true,
                ],

                // Session related configs
                'session' => [

                    // lifetime of a non-authenticated session
                    'default_lifetime' => 3600,

                    // lifetime of a non-authenticated session
                    'default_flash_lifetime' => 3600,

                    // how long before a ref code is considered expired
                    'ref_timeout' => 3600,

                    // random string to make the ref code more random - you can change this, but it will
                    // kill all sessions (forms that are being filled out).
                    'ref_secret' => null,

                    // Flash session cache engine
                    'flash_cache_engine' => null,

                    // Flash session cache pool
                    'flash_cache_pool_read'  => null,
                    'flash_cache_pool_write' => null,
                ],
            ];
        }

        /**
         * Validate the config values
         *
         * @throws config_exception
         */
        public function validate() {

            if (empty($this->config['domain'])) {
                throw new config_exception('"domain" must be set');
            }

            if (empty($this->config['session']['ref_secret'])) {
                throw new config_exception('[\'session\'][\'ref_secret\'] must be set');
            }

            if (empty($this->config['session']['flash_cache_engine'])) {
                throw new config_exception('[\'session\'][\'flash_cache_engine\'] must be set');
            }

            if (empty($this->config['session']['flash_cache_engine'])) {
                throw new config_exception('$this->config[\'session\'][\'flash_cache_engine\'] must be set');
            }

            if (empty($this->config['session']['flash_cache_pool_read'])) {
                throw new config_exception('$this->config[\'session\'][\'flash_cache_pool_read\'] must be set');
            }

            if (empty($this->config['session']['flash_cache_pool_write'])) {
                throw new config_exception('$this->config[\'session\'][\'flash_cache_pool_write\'] must be set');
            }
        }
    }