<?php

    namespace neoform;

    class user_lib {

        public static function default_status() {
            static $status;
            if ($status === null) {
                $status = new user_status_model(core::config()['auth']['default_user_account_status_id']);
            }
            return $status;
        }

        public static function default_hashmethod() {
            static $hashmethod;
            if ($hashmethod === null) {
                $hashmethod = new user_hashmethod_model(core::config()['auth']['default_hash_method_id']);
            }
            return $hashmethod;
        }

        public static function default_hashmethod_cost() {
            return (int) core::config()['auth']['default_hash_method_cost'];
        }

        public static function max_salt_length() {
            return (int) core::config()['auth']['max_salt_length'];
        }

        public static function generate_salt() {
            $salt = '';
            $len = self::max_salt_length();
            for ($i=0; $i < $len; $i++) {
                do {
                    $ord = mt_rand(32, 126);
                } while ($ord === 92); //backslashes are evil
                $salt .= \chr($ord);
            }
            return $salt;
        }

        public static function password_matches(user_model $user, $password) {
            $hash = $user->user_hashmethod()->hash(
                $password,
                $user->password_salt,
                $user->password_cost
            );

            return \strcmp($user->password_hash, $hash) === 0;
        }
    }