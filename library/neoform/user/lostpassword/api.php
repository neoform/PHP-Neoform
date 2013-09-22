<?php

    namespace neoform\user\lostpassword;

    use neoform\input;
    use neoform\entity;
    use neoform\auth;
    use neoform\user;
    use neoform\redirect;
    use neoform\http;
    use neoform;

    class api {

        public static function lost(neoform\site\model $site, array $info) {

            if (auth::instance()->logged_in()) {
                throw new redirect\exception;
            }

            $input = new input\collection($info);

            $input->email->cast('string')->trim()->length(1,255)->is_email()->callback(function($email) use ($site) {
                if (! $email->errors()) {
                    if ($user_id = current(entity::dao('user')->by_email($email->val()))) {
                        $user = new user\model($user_id);
                        if (count(entity::dao('user\site')->by_site_user($site->id, $user->id))) {
                            return $email->data('model', $user);
                        }
                    }
                    $email->errors('Email address not found');
                }
            });

            if ($input->is_valid()) {

                $user = $input->email->data('model');

                //delete all previous reset keys
                entity::dao('user\lostpassword')->delete_multi(
                    new user\lostpassword\collection(
                        entity::dao('user\lostpassword')->by_user($user->id)
                    )
                );

                // Generate random hash
                $hash = neoform\type\string\lib::random_chars(40);

                entity::dao('user\lostpassword')->insert([
                    'user_id' => $user->id,
                    'hash'    => $hash,
                ]);

                //email the request to the friend to tell them the good news
                $email            = new neoform\email\model('password/lost');
                $email->url       = http::instance()->server('surl') . "account/passwordreset/{$hash}";
                $email->site_name = neoform\config::instance()['core']['site_name'];

                try {
                    $email->send($user->email, 'html');
                    return true;
                } catch (neoform\email\exception $e) {
                    throw new neoform\error\exception($e->getMessage());
                }
            }

            throw $input->exception();
        }

        public static function find(neoform\site\model $site, $hash) {
            if (auth::instance()->logged_in()) {
                throw new neoform\redirect\exception;
            }

            try {
                $lost_password = new user\lostpassword\model($hash);
            } catch (user\lostpassword\exception $e) {
                throw new neoform\error\exception('You password reset link has either expired or is not valid');
            }

            $password      = neoform\type\string\lib::random_chars(8, 12);
            $salt          = user\lib::generate_salt();
            $password_cost = user\lib::default_hashmethod_cost();
            $hash_method   = user\lib::default_hashmethod();
            $user          = $lost_password->user();

            entity::dao('user')->update(
                $user,
                [
                    'password_salt'       => $salt,
                    'password_cost'       => $password_cost,
                    'password_hashmethod' => $hash_method->id,
                    'password_hash'       => $hash_method->hash($password, $salt, $password_cost),
                ]
            );

            entity::dao('user\lostpassword')->delete($lost_password);

            return [$user, $password];
        }

        public static function insert(array $info) {

            $input = new input\collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {
                return entity::dao('user\lostpassword')->insert([
                    'hash'      => $input->hash->val(),
                    'user_id'   => $input->user_id->val(),
                    'posted_on' => $input->posted_on->val(),
                ]);
            }
            throw $input->exception();
        }

        public static function update(model $user_lostpassword, array $info, $crush=false) {

            $input = new input\collection($info);

            self::_validate_update($user_lostpassword, $input);

            if ($input->is_valid()) {
                return entity::dao('user\lostpassword')->update(
                    $user_lostpassword,
                    $input->vals(
                        [
                            'hash',
                            'user_id',
                            'posted_on',
                        ],
                        $crush
                    )
                );
            }
            throw $input->exception();
        }

        public static function delete(model $user_lostpassword) {
            return entity::dao('user\lostpassword')->delete($user_lostpassword);
        }

        public static function _validate_insert(input\collection $input) {

            // hash
            $input->hash->cast('string')->length(1, 40)->callback(function($hash) {
                if (entity::dao('user\lostpassword')->record($hash->val())) {
                    $hash->errors('already in use');
                }
            });

            // user_id
            $input->user_id->cast('int')->digit(0, 4294967295)->callback(function($user_id) {
                if (entity::dao('user\lostpassword')->by_user($user_id->val())) {
                    $user_id->errors('already in use');
                }
            })->callback(function($user_id) {
                try {
                    $user_id->data('model', new user\model($user_id->val()));
                } catch (user\exception $e) {
                    $user_id->errors($e->getMessage());
                }
            });

            // posted_on
            $input->posted_on->cast('string')->optional()->is_datetime();
        }

        public static function _validate_update(model $user_lostpassword, input\collection $input) {

            // hash
            $input->hash->cast('string')->optional()->length(1, 40)->callback(function($hash) use ($user_lostpassword) {
                $user_lostpassword_info = entity::dao('user\lostpassword')->record($hash->val());
                if ($user_lostpassword_info && (string) $user_lostpassword_info['hash'] !== $user_lostpassword->hash) {
                    $hash->errors('already in use');
                }
            });

            // user_id
            $input->user_id->cast('int')->optional()->digit(0, 4294967295)->callback(function($user_id) use ($user_lostpassword) {
                $hash_arr = entity::dao('user\lostpassword')->by_user($user_id->val());
                if (is_array($hash_arr) && $hash_arr && (string) current($hash_arr) !== $user_lostpassword->hash) {
                    $user_id->errors('already in use');
                }
            })->callback(function($user_id) {
                try {
                    $user_id->data('model', new user\model($user_id->val()));
                } catch (user\exception $e) {
                    $user_id->errors($e->getMessage());
                }
            });

            // posted_on
            $input->posted_on->cast('string')->optional()->is_datetime();
        }
    }
