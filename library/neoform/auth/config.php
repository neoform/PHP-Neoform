<?php

    namespace neoform\auth;

    use neoform;

    class config extends neoform\config\model {

        /**
         * The array key this config file uses in the compiled configs
         *
         * @return string
         */
        public function get_array_key() {
            return 'auth';
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
         * @throws neoform\config\exception
         */
        public function validate() {
            if (empty($this->config['login_account_statuses'])) {
                throw new neoform\config\exception('"login_account_statuses" must have at least one status');
            }
        }

        /**
         * Validate the config values after the config has been compiled
         */
        public function validate_post(array $config) {

        }
    }