<?php

    class user_lostpassword_api {

        public static function lost(site_model $site, array $info) {

            if (core::auth()->logged_in()) {
                throw new redirect_exception;
            }

            $input = new input_collection($info);

            $input->email->cast('string')->trim()->length(1,255)->is_email()->callback(function($email) use ($site) {
                if (! $email->errors()) {
                    if ($user_id = current(user_dao::by_email($email->val()))) {
                        $user = new user_model($user_id);
                        if (count(user_site_dao::by_site_user($site->id, $user->id))) {
                            return $email->data('model', $user);
                        }
                    }
                    $email->errors('Email address not found');
                }
            });

            if ($input->is_valid()) {

                $user = $input->email->data('model');

                //delete all previous reset keys
                user_lostpassword_dao::deletes(
                    new user_lostpassword_collection(
                        user_lostpassword_dao::by_user($user->id)
                    )
                );

                // Generate random hash
                $hash = type_string_lib::random_chars(40);

                user_lostpassword_dao::insert([
                    'user_id' => $user->id,
                    'hash'    => $hash,
                ]);

                //email the request to the friend to tell them the good news
                $email            = new email_model('password/lost');
                $email->url       = core::http()->server('surl') . 'account/passwordreset/' . $hash;
                $email->site_name = core::config()->system['site_name'];

                try {
                    $email->send($user->email, 'html');
                    return true;
                } catch (exception_mail $e) {
                    throw new error_exception($e->getMessage());
                }
            }

            throw $input->exception();
        }

        public static function find(site_model $site, $hash) {
            if (core::auth()->logged_in()) {
                throw new redirect_exception;
            }

            try {
                $lost_password = new user_lostpassword_model($hash);
            } catch (user_lostpassword_exception $e) {
                throw new error_exception('You password reset link has either expired or is not valid');
            }

            $password      = type_string_lib::random_chars(8, 12);
            $salt          = user_lib::generate_salt();
            $password_cost = user_lib::default_hashmethod_cost();
            $hash_method   = user_lib::default_hashmethod();
            $user          = $lost_password->user();

            user_dao::update(
                $user,
                array(
                    'password_salt'       => $salt,
                    'password_cost'       => $password_cost,
                    'password_hashmethod' => $hash_method->id,
                    'password_hash'       => $hash_method->hash($password, $salt, $password_cost),
                )
            );

            user_lostpassword_dao::delete($lost_password);

            return [$user, $password];
        }

        public static function insert(array $info) {

            $input = new input_collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {
                return user_lostpassword_dao::insert([
                    'hash'      => $input->hash->val(),
                    'user_id'   => $input->user_id->val(),
                    'posted_on' => $input->posted_on->val(),
                ]);
            }
            throw $input->exception();
        }

        public static function update(user_lostpassword_model $user_lostpassword, array $info, $crush=false) {

            $input = new input_collection($info);

            self::_validate_update($user_lostpassword, $input);

            if ($input->is_valid()) {
                return user_lostpassword_dao::update(
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

        public static function _validate_insert(input_collection $input) {

            // hash
            $input->hash->cast('string')->length(1, 20);

            // user_id
            $input->user_id->cast('int')->digit(0, 4294967295)->callback(function($user_id) {
                if (user_lostpassword_dao::by_user($user_id->val())) {
                    $user_id->errors('already in use');
                }
            })->callback(function($user_id){
                try {
                    $user_id->data('model', new user_model($user_id->val()));
                } catch (user_exception $e) {
                    $user_id->errors($e->getMessage());
                }
            });

            // posted_on
            $input->posted_on->cast('string')->optional()->is_datetime();
        }

        public static function _validate_update(user_lostpassword_model $user_lostpassword, input_collection $input) {

            // hash
            $input->hash->cast('string')->optional()->length(1, 20);

            // user_id
            $input->user_id->cast('int')->optional()->digit(0, 4294967295)->callback(function($user_id) use ($user_lostpassword) {
                $hash_arr = user_lostpassword_dao::by_user($user_id->val());
                if (is_array($hash_arr) && count($hash_arr) && (string) current($hash_arr) !== $user_lostpassword->hash) {
                    $user_id->errors('already in use');
                }
            })->callback(function($user_id){
                try {
                    $user_id->data('model', new user_model($user_id->val()));
                } catch (user_exception $e) {
                    $user_id->errors($e->getMessage());
                }
            });

            // posted_on
            $input->posted_on->cast('string')->optional()->is_datetime();
        }
    }
