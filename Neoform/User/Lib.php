<?php

    namespace Neoform\User;

    use Neoform;

    class Lib {

        /**
         * @return status\Model
         */
        public static function default_status() {
            static $status;
            if ($status === null) {
                $status = new Status\Model(Neoform\Auth\Config::get()->getDefaultUserAccountStatusId());
            }
            return $status;
        }

        /**
         * @return hashmethod\Model
         */
        public static function default_hashmethod() {
            static $hashmethod;
            if ($hashmethod === null) {
                $hashmethod = new Hashmethod\Model(Neoform\Auth\Config::get()->getDefaultHashMethodId());
            }
            return $hashmethod;
        }

        /**
         * @return int
         */
        public static function default_hashmethod_cost() {
            return (int) Neoform\Auth\Config::get()->getDefaultHashMethodCost();
        }

        /**
         * @return int
         */
        public static function max_salt_length() {
            return (int) Neoform\Auth\Config::get()->getMaxSaltLength();
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
        public static function password_matches(Model $user, $password) {
            $hash = $user->user_hashmethod()->hash(
                $password,
                $user->password_salt,
                $user->password_cost
            );

            return strcmp($user->password_hash, $hash) === 0;
        }
    }
