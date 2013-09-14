<?php

    namespace neoform\auth;

    use neoform\input;
    use neoform\entity;

    class api {

        /**
         * @param array $info
         *
         * @return mixed
         * @throws input\exception
         */
        public static function insert(array $info) {

            $input = new input\collection($info);

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
         * @param model $auth
         * @param array $info
         * @param bool  $crush
         *
         * @return mixed
         * @throws input\exception
         */
        public static function update(model $auth, array $info, $crush=false) {

            $input = new input\collection($info);

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

        /**
         * @param \neoform\site\model $site
         * @param array               $info
         *
         * @return model
         * @throws input\exception
         */
        public static function login(\neoform\site\model $site, array $info) {

            $input = new input\collection($info);

            $attemtped_user = null;

            $input->email->cast('string')->trim()->tolower()->length(1, 255)->is_email()->callback(function($email) use (& $attemtped_user, $site) {
                try {
                    if ($user_id = current(entity::dao('user')->by_email($email->val()))) {
                        if (count(entity::dao('user\site')->by_site_user($site->id, $user_id))) {
                            return $attemtped_user = new \neoform\user\model($user_id);
                        }
                    }
                } catch (\neoform\user\exception $e) {

                }
                $email->errors('Your email address or password is incorrect.');
            });
            $input->remember->cast('bool');
            $input->password->cast('string')->callback(function($password) use ($attemtped_user) {
                // Verify password matches
                if ($attemtped_user && ! \neoform\user\lib::password_matches($attemtped_user, $password->val())) {
                    $password->errors('Your email address or password is incorrect.');

                // Make sure account is active
                } else if ($attemtped_user && ! $attemtped_user->is_active()) {
                    $password->errors('You cannot log in with this account at this time');
                }
            });

            if ($input->is_valid()) {
                $auth = lib::activate_session(
                    $attemtped_user,
                    $input->remember->val()
                );
                return $auth;
            }

            throw $input->exception();
        }

        /**
         * @param model $auth
         *
         * @return bool
         */
        public static function logout(model $auth) {
            entity::dao('auth')->delete($auth);
            $auth->reset();
            return true;
        }

        /**
         * @param input\collection $input
         */
        public static function _validate_insert(input\collection $input) {

            // hash
            $input->hash->cast('binary')->length(1, 40)->callback(function($hash) {
                if (entity::dao('auth')->record($hash->val())) {
                    $hash->errors('already in use');
                }
            });

            // user_id
            $input->user_id->cast('int')->digit(0, 4294967295)->callback(function($user_id) {
                try {
                    $user_id->data('model', new \neoform\user\model($user_id->val()));
                } catch (\neoform\user\exception $e) {
                    $user_id->errors($e->getMessage());
                }
            });

            // expires_on
            $input->expires_on->cast('string')->optional()->is_datetime();
        }

        /**
         * @param model            $auth
         * @param input\collection $input
         */
        public static function _validate_update(model $auth, input\collection $input) {

            // hash
            $input->hash->cast('binary')->optional()->length(1, 40)->callback(function($hash) use ($auth) {
                $auth_info = entity::dao('auth')->record($hash->val());
                if ($auth_info && (binary) $auth_info['hash'] !== $auth->hash) {
                    $hash->errors('already in use');
                }
            });

            // user_id
            $input->user_id->cast('int')->optional()->digit(0, 4294967295)->callback(function($user_id) {
                try {
                    $user_id->data('model', new \neoform\user\model($user_id->val()));
                } catch (\neoform\user\exception $e) {
                    $user_id->errors($e->getMessage());
                }
            });

            // expires_on
            $input->expires_on->cast('string')->optional()->is_datetime();
        }
    }
