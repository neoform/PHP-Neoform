<?php

    class user_api {

        public static function insert(array $info) {

            $input = new input_collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {

                $hashmethod      = user_lib::default_hashmethod();
                $hashmethod_cost = user_lib::default_hashmethod_cost();
                $salt             = user_lib::generate_salt();

                $hash = user_lib::hash($input->password1->val(), $salt, $hashmethod, $hashmethod_cost);

                $user = user_dao::insert([
                    'email'               => $input->email->val(),
                    'password_hash'       => $hash,
                    'password_hashmethod' => $hashmethod->id,
                    'password_cost'       => $hashmethod_cost,
                    'password_salt'       => $salt,
                    'status_id'           => user_lib::default_status()->id,
                ]);

                user_date_dao::insert([
                    'user_id' => $user->id,
                ]);

                return $user;
            }
            throw $input->exception();
        }

        public static function update_email(user_model $user, array $info) {

            $input = new input_collection($info);

            self::_validate_update_email($user, $input);

            if ($input->is_valid()) {
                return user_dao::update(
                    $user,
                    [
                        'email' => $input->email->val(),
                    ]
                );
            }
            throw $input->exception();
        }

        public static function update_password(user_model $user, array $info) {

            $input = new input_collection($info);

            self::_validate_update_password($user, $input);

            if ($input->is_valid()) {

                $salt          = user_lib::generate_salt();
                $password_cost = user_lib::default_hashmethod_cost();
                $hash_method   = user_lib::default_hashmethod();

                return user_dao::update(
                    $user,
                    [
                        'password_salt'       => $salt,
                        'password_cost'       => $password_cost,
                        'password_hashmethod' => $hash_method->id,
                        'password_hash'       => user_lib::hash(
                            $input->password1->val(),
                            $salt,
                            $hash_method,
                            $password_cost
                        ),
                    ]
                );
            }
            throw $input->exception();
        }

        public static function email_available(array $info) {

            $input = new input_collection($info);

            $input->email->cast('string')->trim()->tolower()->is_email();
            if ($input->is_valid()) {
                return ! (bool) current(user_dao::by_email($input->email->val()));
            } else {
                throw $input->exception();
            }
        }

        public static function _validate_insert(input_collection $input) {

            // email
            $input->email->cast('string')->length(1, 255)->is_email()->callback(function($email) {
                $id_arr = user_dao::by_email($email->val());
                if (is_array($id_arr) && count($id_arr)) {
                    $email->errors('already in use');
                }
            });

            // password1
            $input->password1->cast('string')->length(6, 1000);

            // password2
            $input->password2->cast('string')->length(6, 1000);

            if ($input->password1->val() !== $input->password2->val()) {
                $input->password2->errors('password does not match');
            }
        }

        public static function _validate_update_email(user_model $user, input_collection $input) {

            // email
            $input->email->cast('string')->length(1, 255)->is_email()->callback(function($email) use ($user) {
                $id_arr = user_dao::by_email($email->val());
                if (is_array($id_arr) && count($id_arr) && (int) current($id_arr) !== $user->id) {
                    $email->errors('already in use');
                }
            });
        }

        public static function _validate_update_password(user_model $user, input_collection $input) {

            // current password
            $input->current_password->cast('string')->callback(function($password) use ($user) {
                if (! user_lib::password_matches($user, $password->val())) {
                    $password->errors('does not match');
                }
            });

            // password1
            $input->password1->cast('string')->length(6, 1000);

            // password2
            $input->password2->cast('string')->length(6, 1000);

            if (! $input->password1->errors() && ! $input->password2->errors() && $input->password1->val() !== $input->password2->val()) {
                $input->password2->errors('password does not match');
            }
        }

        public static function _validate_admin_insert(input_collection $input) {

            // email
            $input->email->cast('string')->length(1, 255)->callback(function($email) {
                $id_arr = user_dao::by_email($email->val());
                if (is_array($id_arr) && count($id_arr)) {
                    $email->errors('already in use');
                }
            });

            // password_hash
            $input->password_hash->cast('string')->length(1, 255);

            // password_hashmethod
            $input->password_hashmethod->cast('int')->digit(0, 255)->callback(function($password_hashmethod){
                try {
                    $password_hashmethod->data('model', new user_hashmethod_model($password_hashmethod->val()));
                } catch (user_hashmethod_exception $e) {
                    $password_hashmethod->errors($e->getMessage());
                }
            });

            // password_cost
            $input->password_cost->cast('int')->digit(0, 4294967295);

            // password_salt
            $input->password_salt->cast('string')->length(1, 40);

            // status
            $input->status_id->cast('int')->digit(0, 255)->callback(function($status_id){
                try {
                    $status_id->data('model', new user_status_model($status_id->val()));
                } catch (user_status_exception $e) {
                    $status_id->errors($e->getMessage());
                }
            });
        }

        public static function _validate_admin_update(user_model $user, input_collection $input) {

            // email
            $input->email->cast('string')->optional()->length(1, 255)->callback(function($email) use ($user) {
                $id_arr = user_dao::by_email($email->val());
                if (is_array($id_arr) && count($id_arr) && (int) current($id_arr) !== $user->id) {
                    $email->errors('already in use');
                }
            });

            // password_hash
            $input->password_hash->cast('string')->optional()->length(1, 255);

            // password_hashmethod
            $input->password_hashmethod->cast('int')->optional()->digit(0, 255)->callback(function($password_hashmethod){
                try {
                    $password_hashmethod->data('model', new user_hashmethod_model($password_hashmethod->val()));
                } catch (user_hashmethod_exception $e) {
                    $password_hashmethod->errors($e->getMessage());
                }
            });

            // password_cost
            $input->password_cost->cast('int')->optional()->digit(0, 4294967295);

            // password_salt
            $input->password_salt->cast('string')->optional()->length(1, 40);

            // status
            $input->status_id->cast('int')->optional()->digit(0, 255)->callback(function($status_id){
                try {
                    $status_id->data('model', new user_status_model($status_id->val()));
                } catch (user_status_exception $e) {
                    $status_id->errors($e->getMessage());
                }
            });
        }

    }
