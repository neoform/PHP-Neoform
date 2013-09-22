<?php

    namespace neoform\user;

    use neoform;

    class lib {

        /**
         * @return status\model
         */
        public static function default_status() {
            static $status;
            if ($status === null) {
                $status = new status\model(neoform\config::instance()['auth']['default_user_account_status_id']);
            }
            return $status;
        }

        /**
         * @return hashmethod\model
         */
        public static function default_hashmethod() {
            static $hashmethod;
            if ($hashmethod === null) {
                $hashmethod = new hashmethod\model(neoform\config::instance()['auth']['default_hash_method_id']);
            }
            return $hashmethod;
        }

        /**
         * @return int
         */
        public static function default_hashmethod_cost() {
            return (int) neoform\config::instance()['auth']['default_hash_method_cost'];
        }

        /**
         * @return int
         */
        public static function max_salt_length() {
            return (int) neoform\config::instance()['auth']['max_salt_length'];
        }

        /**
         * @return string
         */
        public static function generate_salt() {
            $salt = '';
            $len = self::max_salt_length();
            for ($i=0; $i < $len; $i++) {
                do {
                    $ord = mt_rand(32, 126);
                } while ($ord === 92); //backslashes are evil
                $salt .= chr($ord);
            }
            return $salt;
        }

        /**
         * @param model  $user
         * @param string $password
         *
         * @return bool
         */
        public static function password_matches(model $user, $password) {
            $hash = $user->user_hashmethod()->hash(
                $password,
                $user->password_salt,
                $user->password_cost
            );

            return strcmp($user->password_hash, $hash) === 0;
        }
    }
