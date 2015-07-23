<?php

    namespace Neoform\Auth;

    use Neoform;

    class Config extends Neoform\Config\Model {

        /**
         * the normal auth session lifespan [3 months in seconds] [required]
         *
         * @return string
         */
        public function getNormalAuthLifetime() {
            return $this->values['normal_auth_lifetime'];
        }

        /**
         * the long auth session lifespan  [1 year in seconds] [required]
         *
         * @return string
         */
        public function getLongAuthLifetime() {
            return $this->values['long_auth_lifetime'];
        }

        /**
         * how long can the password salt be
         *
         * @return integer
         */
        public function getMaxSaltLength() {
            return $this->values['max_salt_length'];
        }

        /**
         * Password hash method
         *
         * @return integer
         */
        public function getDefaultHashMethodId() {
            return $this->values['default_hash_method_id'];
        }

        /**
         * User account status
         *
         * @return integer
         */
        public function getDefaultUserAccountStatusId() {
            return $this->values['default_user_account_status_id'];
        }

        /**
         * Hash method rounds (cost)
         *
         * @return integer
         */
        public function getDefaultHashMethodCost() {
            return $this->values['default_hash_method_cost'];
        }

        /**
         * Authentication cookie name
         *
         * @return string
         */
        public function getCookie() {
            return $this->values['cookie'];
        }

        /**
         * Which site does this user account belong to
         *
         * @return string
         */
        public function getSite() {
            return $this->values['site'];
        }

        /**
         * What user_statuses can a user log in with - key is the ID, value is the name
         *
         * @return string
         */
        public function getLoginAccountStatuses() {
            return $this->values['login_account_statuses'];
        }
    }