<?php

    namespace Neoform\Auth\Config;

    use Neoform;

    class Builder extends Neoform\Config\Builder {

        /**
         * @return Neoform\Auth\Config
         */
        public function build() {
            return new Neoform\Auth\Config($this->configValues);
        }

        /**
         * Config default values
         *
         * @return array
         */
        protected function defaults() {
            return [
                // the normal auth session lifespan [3 months in seconds] [required]
                'normal_auth_lifetime' => '3M',

                // the long auth session lifespan  [1 year in seconds] [required]
                'long_auth_lifetime' => '1Y',

                // how long can the password salt be
                'max_salt_length' => 40,

                // default password hash method
                'default_hash_method_id' => 1,

                // default user account status
                'default_user_account_status_id' => 1,

                // default user account status
                'default_hash_method_cost' => 10,

                // authentication cookie name [required]
                'cookie' => 'cc',

                // Which site does this user account belong to
                'site' => 'main',

                // What user_statuses can a user log in with - key is the ID, value is the name
                'login_account_statuses' => [
                    1 => 'active',
                ],
            ];
        }

        /**
         * Validate the config values
         *
         * @throws Neoform\Config\Exception
         */
        public function validate() {
            if (empty($this->configValues['login_account_statuses'])) {
                throw new Neoform\Config\Exception('"login_account_statuses" must have at least one status');
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