<?php

    namespace Neoform\User\Date;

    use Neoform\Input;
    use Neoform\Entity;

    class Api {

        /**
         * Creates a User Date model with $info
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
                return Entity::dao('Neoform\User\Date')->insert([
                    'user_id'             => $input->user_id->val(),
                    'created_on'          => $input->created_on->val(),
                    'last_login'          => $input->last_login->val(),
                    'email_verified_on'   => $input->email_verified_on->val(),
                    'password_updated_on' => $input->password_updated_on->val(),
                ]);
            }
            throw $input->exception();
        }

        /**
         * Update a User Date model with $info
         *
         * @param model $user_date
         * @param array $info
         * @param bool  $crush
         *
         * @return model
         * @throws Input\Exception
         */
        public static function update(Model $user_date, array $info, $crush=false) {

            $input = new Input\Collection($info);

            self::_validate_update($user_date, $input);

            if ($input->is_valid()) {
                return Entity::dao('Neoform\User\Date')->update(
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

        /**
         * Delete a User Date
         *
         * @param model $user_date
         *
         * @return bool
         */
        public static function delete(Model $user_date) {
            return Entity::dao('Neoform\User\Date')->delete($user_date);
        }

        /**
         * Validates info to for insert
         *
         * @param Input\Collection $input
         */
        public static function _validate_insert(Input\Collection $input) {

            // user_id
            $input->user_id->cast('int')->digit(0, 4294967295)->callback(function($user_id) {
                if (Entity::dao('Neoform\User\Date')->record($user_id->val())) {
                    $user_id->errors('already in use');
                }
            })->callback(function($user_id) {
                try {
                    $user_id->data('model', new \Neoform\User\Model($user_id->val()));
                } catch (\Neoform\User\Exception $e) {
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

        /**
         * Validates info to update a User Date model
         *
         * @param model $user_date
         * @param Input\Collection $input
         */
        public static function _validate_update(Model $user_date, Input\Collection $input) {

            // user_id
            $input->user_id->cast('int')->optional()->digit(0, 4294967295)->callback(function($user_id) use ($user_date) {
                $user_date_info = Entity::dao('Neoform\User\Date')->record($user_id->val());
                if ($user_date_info && (int) $user_date_info['user_id'] !== $user_date->user_id) {
                    $user_id->errors('already in use');
                }
            })->callback(function($user_id) {
                try {
                    $user_id->data('model', new \Neoform\User\Model($user_id->val()));
                } catch (\Neoform\User\Exception $e) {
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
