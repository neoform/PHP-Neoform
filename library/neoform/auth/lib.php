<?php

    namespace neoform\auth;

    use neoform;
    use dateinterval;
    use datetime;

    class lib {

        /**
         * Activate an auth session
         *
         * @param neoform\user\model $user
         * @param bool               $remember
         *
         * @return model the newly created session
         */
        public static function activate_session(neoform\user\model $user, $remember=false) {

            // Create expiry date for session
            $expires = new datetime;
            if ($remember) {
                $expires->add(new dateinterval('P' . neoform\config::instance()['auth']['long_auth_lifetime']));
            } else {
                $expires->add(new dateinterval('P' . neoform\config::instance()['auth']['normal_auth_lifetime']));
            }

            // Create session
            $return = neoform\entity::dao('auth')->insert([
                'user_id'    => $user->id,
                'expires_on' => $expires->format('Y-m-d H:i:s'),
                'hash'       => self::get_hash_cookie(),
            ]);

            // Update last login
            $now = new neoform\type\date;
            neoform\entity::dao('user\date')->update(
                $user->user_date(),
                [
                    'last_login' => $now->datetime(),
                ]
            );

            return $return;
        }

        /**
         * Get auth cookie
         *
         * @return null|string
         */
        public static function get_hash_cookie() {
            if (strlen($hash = neoform\http::instance()->cookie(neoform\config::instance()['auth']['cookie'])) === 40) {
                return $hash;
            } else {
                return self::create_hash_cookie();
            }
        }

        /**
         * Create auth cookie
         *
         * @param string|null $hash hash to be used, default null means function will create a hash
         *
         * @return string
         */
        public static function create_hash_cookie($hash=null) {

            if ($hash === null) {
                $hash = self::generate_hash();
            }

            neoform\output::instance()->cookie_set(
                neoform\config::instance()['auth']['cookie'],
                $hash
            );

            return $hash;
        }

        /**
         * Generate Hash for cookie
         *
         * @return string
         */
        public static function generate_hash() {
            return neoform\encrypt\lib::rand(40);
        }
    }
