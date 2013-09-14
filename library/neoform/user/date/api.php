<?php

    namespace neoform\user\date;

    use neoform\input;
    use neoform\entity;

    class api {

        public static function insert(array $info) {

            $input = new input\collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {
                return entity::dao('user\date')->insert([
                    'user_id'             => $input->user_id->val(),
                    'created_on'          => $input->created_on->val(),
                    'last_login'          => $input->last_login->val(),
                    'email_verified_on'   => $input->email_verified_on->val(),
                    'password_updated_on' => $input->password_updated_on->val(),
                ]);
            }
            throw $input->exception();
        }

        public static function update(model $user_date, array $info, $crush=false) {

            $input = new input\collection($info);

            self::_validate_update($user_date, $input);

            if ($input->is_valid()) {
                return entity::dao('user\date')->update(
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

        public static function delete(model $user_date) {
            return entity::dao('user\date')->delete($user_date);
        }

        public static function _validate_insert(input\collection $input) {

            // user_id
            $input->user_id->cast('int')->digit(0, 4294967295)->callback(function($user_id) {
                if (entity::dao('user\date')->record($user_id->val())) {
                    $user_id->errors('already in use');
                }
            })->callback(function($user_id) {
                try {
                    $user_id->data('model', new \neoform\user\model($user_id->val()));
                } catch (\neoform\user\exception $e) {
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

        public static function _validate_update(model $user_date, input\collection $input) {

            // user_id
            $input->user_id->cast('int')->optional()->digit(0, 4294967295)->callback(function($user_id) use ($user_date) {
                $user_date_info = entity::dao('user\date')->record($user_id->val());
                if ($user_date_info && (int) $user_date_info['user_id'] !== $user_date->user_id) {
                    $user_id->errors('already in use');
                }
            })->callback(function($user_id) {
                try {
                    $user_id->data('model', new \neoform\user\model($user_id->val()));
                } catch (\neoform\user\exception $e) {
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
