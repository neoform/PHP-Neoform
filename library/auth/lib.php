<?php

    class auth_lib {

        /**
         * Activate an auth session
         *
         * @param user_model $user
         * @param bool       $remember
         *
         * @return auth_model the newly created session
         */
        public static function activate_session(user_model $user, $remember=false) {

            // Create expiry date for session
            $expires = new datetime();
            if ($remember) {
                $expires->add(new DateInterval('P' . core::config()->auth['long_auth_lifetime']));
            } else {
                $expires->add(new DateInterval('P' . core::config()->auth['normal_auth_lifetime']));
            }

            // Create session
            $return = auth_dao::insert([
                'user_id'    => $user->id,
                'expires_on' => $expires->format('Y-m-d H:i:s'),
                'hash'       => self::get_hash_cookie(),
            ]);

            // Update last login
            $now = new type_date();
            user_date_dao::update(
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
            if (strlen($hash = core::http()->cookie(core::config()->auth['cookie'])) === 40) {
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

            core::output()->cookie_set(
                core::config()->auth['cookie'],
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
            return encrypt_lib::rand(40);
        }
    }