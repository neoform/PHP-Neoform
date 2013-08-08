<?php

    /**
     * Auth API
     */
    class auth_api {

        /**
         * @param array $info
         *
         * @return auth_model
         * @throws input_exception
         */
        public static function insert(array $info) {

            $input = new input_collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {
                return entity::dao('auth')->insert([
                    'hash'       => $input->hash->val(),
                    'user_id'    => $input->user_id->val(),
                    'expires_on' => $input->expires_on->val(),
                ]);
            }
            throw $input->exception();
        }

        /**
         * @param auth_model $auth
         * @param array      $info
         * @param bool       $crush
         *
         * @return model|bool
         * @throws input_exception
         */
        public static function update(auth_model $auth, array $info, $crush=false) {

            $input = new input_collection($info);

            self::_validate_update($auth, $input);

            if ($input->is_valid()) {
                return entity::dao('auth')->update(
                    $auth,
                    $input->vals(
                        [
                            'hash',
                            'user_id',
                            'expires_on',
                        ],
                        $crush
                    )
                );
            }
            throw $input->exception();
        }

        public static function login(site_model $site, array $info) {

            $input = new input_collection($info);

            $attemtped_user = null;

            $input->email->cast('string')->trim()->tolower()->length(1, 255)->is_email()->callback(function($email) use (& $attemtped_user, $site) {
                try {
                    if ($user_id = current(entity::dao('user')->by_email($email->val()))) {
                        if (count(entity::dao('user_site')->by_site_user($site->id, $user_id))) {
                            return $attemtped_user = new user_model($user_id);
                        }
                    }
                } catch (user_exception $e) {

                }
                $email->errors('Your email address or password is incorrect.');
            });
            $input->remember->cast('bool');
            $input->password->cast('string')->callback(function($password) use ($attemtped_user) {
                // Verify password matches
                if ($attemtped_user && ! user_lib::password_matches($attemtped_user, $password->val())) {
                    $password->errors('Your email address or password is incorrect.');

                // Make sure account is active
                } else if (! in_array($attemtped_user->user_status()->name, core::config()['auth']['login_account_statuses'])) {
                    $password->errors('You cannot log in with this account at this time');
                }
            });

            if ($input->is_valid()) {
                $auth = auth_lib::activate_session(
                    $attemtped_user,
                    $input->remember->val()
                );
                return $auth;
            }

            throw $input->exception();
        }

        public static function logout(auth_model $auth) {
            entity::dao('auth')->delete($auth);
            $auth->reset();
            return true;
        }

        public static function _validate_insert(input_collection $input) {

            // hash
            $input->hash->cast('string')->length(1, 20);

            // user_id
            $input->user_id->cast('int')->digit(0, 4294967295)->callback(function($user_id){
                try {
                    $user_id->data('model', new user_model($user_id->val()));
                } catch (user_exception $e) {
                    $user_id->errors($e->getMessage());
                }
            });

            // expires_on
            $input->expires_on->cast('string')->optional()->is_datetime();
        }

        public static function _validate_update(auth_model $auth, input_collection $input) {

            // hash
            $input->hash->cast('string')->optional()->length(1, 20);

            // user_id
            $input->user_id->cast('int')->optional()->digit(0, 4294967295)->callback(function($user_id){
                try {
                    $user_id->data('model', new user_model($user_id->val()));
                } catch (user_exception $e) {
                    $user_id->errors($e->getMessage());
                }
            });

            // expires_on
            $input->expires_on->cast('string')->optional()->is_datetime();
        }

    }
