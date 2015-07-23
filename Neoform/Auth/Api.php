<?php

    namespace Neoform\Auth;

    use Neoform\Input;
    use Neoform\Entity;
    use Neoform;

    class Api {

        /**
         * Creates a Auth model with $info
         *
         * @param array $info
         *
         * @return model
         * @throws Input\Exception
         */
        public static function insert(array $info) {

            $input = new Input\Collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {
                return Entity::dao('Neoform\Auth')->insert([
                    'hash'       => $input->hash->val(),
                    'user_id'    => $input->user_id->val(),
                    'expires_on' => $input->expires_on->val(),
                ]);
            }
            throw $input->exception();
        }

        /**
         * Update a Auth model with $info
         *
         * @param model $auth
         * @param array $info
         * @param bool  $crush
         *
         * @return model
         * @throws Input\Exception
         */
        public static function update(Model $auth, array $info, $crush=false) {

            $input = new Input\Collection($info);

            self::_validate_update($auth, $input);

            if ($input->is_valid()) {
                return Entity::dao('Neoform\Auth')->update(
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
         * @param Neoform\Session\Auth $auth
         * @param Neoform\Site\Model   $site
         * @param array                $info
         *
         * @return model
         * @throws Input\Exception
         */
        public static function verifyLogin(Neoform\Session\Auth $auth, Neoform\Site\Model $site, array $info) {

            $input = new Input\Collection($info);

            $attemtped_user = null;

            $input->email->cast('string')->trim()->tolower()->length(1, 255)->is_email()->callback(function($email) use (& $attemtped_user, $site) {
                try {
                    if ($user_id = current(Entity::dao('Neoform\User')->by_email($email->val()))) {
                        if (count(Entity::dao('Neoform\User\Site')->by_site_user($site->id, $user_id))) {
                            $attemtped_user = new \Neoform\User\Model($user_id);
                            return;
                        }
                    }
                } catch (\Neoform\User\Exception $e) {

                }
                $email->errors('Your email address or password is incorrect.');
            });
            $input->remember->cast('bool');
            $input->password->cast('string')->callback(function($password) use ($attemtped_user) {
                // Verify password matches
                if ($attemtped_user && ! \Neoform\User\Lib::password_matches($attemtped_user, $password->val())) {
                    $password->errors('Your email address or password is incorrect.');

                // Make sure account is active
                } else if ($attemtped_user && ! $attemtped_user->is_active()) {
                    $password->errors('You cannot log in with this account at this time');
                }
            });

            if ($input->is_valid()) {
                $auth->setUser(
                    $attemtped_user,
                    $input->remember->val()
                );
                return true;
            }

            throw $input->exception();
        }

        /**
         * Validates info to for insert
         *
         * @param Input\Collection $input
         */
        public static function _validate_insert(Input\Collection $input) {

            // hash
            $input->hash->cast('binary')->length(1, 40)->callback(function($hash) {
                if (Entity::dao('Neoform\Auth')->record($hash->val())) {
                    $hash->errors('already in use');
                }
            });

            // user_id
            $input->user_id->cast('int')->digit(0, 4294967295)->callback(function($user_id) {
                try {
                    $user_id->data('model', new \Neoform\User\Model($user_id->val()));
                } catch (\Neoform\User\Exception $e) {
                    $user_id->errors($e->getMessage());
                }
            });

            // expires_on
            $input->expires_on->cast('string')->optional()->is_datetime();
        }

        /**
         * Validates info to update a Auth model
         *
         * @param model $auth
         * @param Input\Collection $input
         */
        public static function _validate_update(Model $auth, Input\Collection $input) {

            // hash
            $input->hash->cast('binary')->optional()->length(1, 40)->callback(function($hash) use ($auth) {
                $auth_info = Entity::dao('Neoform\Auth')->record($hash->val());
                if ($auth_info && (binary) $auth_info['hash'] !== $auth->hash) {
                    $hash->errors('already in use');
                }
            });

            // user_id
            $input->user_id->cast('int')->optional()->digit(0, 4294967295)->callback(function($user_id) {
                try {
                    $user_id->data('model', new \Neoform\User\Model($user_id->val()));
                } catch (\Neoform\User\Exception $e) {
                    $user_id->errors($e->getMessage());
                }
            });

            // expires_on
            $input->expires_on->cast('string')->optional()->is_datetime();
        }
    }
