<?php

    class user_date_api {

        public static function insert(array $info) {

            $input = new input_collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {
                return user_date_dao::insert([
                    'user_id'             => $input->user_id->val(),
                    'created_on'          => $input->created_on->val(),
                    'last_login'          => $input->last_login->val(),
                    'email_verified_on'   => $input->email_verified_on->val(),
                    'password_updated_on' => $input->password_updated_on->val(),
                ]);
            }
            throw $input->exception();
        }

        public static function update(user_date_model $user_date, array $info, $crush=false) {

            $input = new input_collection($info);

            self::_validate_update($user_date, $input);

            if ($input->is_valid()) {
                return user_date_dao::update(
                    $user_date,
                    $input->vals(
                        [
                            'user_id',
                            'created_on',
                            'last_login',
                            'email_verified_on',
                            'password_updated_on',
                        ],
                        $crush
                    )
                );
            }
            throw $input->exception();
        }

        public static function delete(user_date_model $user_date) {
            return user_date_dao::delete($user_date);
        }

        public static function _validate_insert(input_collection $input) {

            // user_id
            $input->user_id->cast('int')->digit(0, 4294967295)->callback(function($user_id){
                try {
                    $user_id->data('model', new user_model($user_id->val()));
                } catch (user_exception $e) {
                    $user_id->errors($e->getMessage());
                }
            });

            // created_on
            $input->created_on->cast('string')->optional()->is_datetime();

            // last_login
            $input->last_login->cast('string')->optional()->is_datetime();

            // email_verified_on
            $input->email_verified_on->cast('string')->optional()->is_datetime();

            // password_updated_on
            $input->password_updated_on->cast('string')->optional()->is_datetime();
        }

        public static function _validate_update(user_date_model $user_date, input_collection $input) {

            // user_id
            $input->user_id->cast('int')->optional()->digit(0, 4294967295)->callback(function($user_id){
                try {
                    $user_id->data('model', new user_model($user_id->val()));
                } catch (user_exception $e) {
                    $user_id->errors($e->getMessage());
                }
            });

            // created_on
            $input->created_on->cast('string')->optional()->is_datetime();

            // last_login
            $input->last_login->cast('string')->optional()->is_datetime();

            // email_verified_on
            $input->email_verified_on->cast('string')->optional()->is_datetime();

            // password_updated_on
            $input->password_updated_on->cast('string')->optional()->is_datetime();
        }

    }
