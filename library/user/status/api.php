<?php

    class user_status_api {

        public static function insert(array $info) {

            $input = new input_collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {
                return user_status_dao::insert([
                    'id'   => $input->id->val(),
                    'name' => $input->name->val(),
                ]);
            }
            throw $input->exception();
        }

        public static function update(user_status_model $user_status, array $info, $crush=false) {

            $input = new input_collection($info);

            self::_validate_update($user_status, $input);

            if ($input->is_valid()) {
                return user_status_dao::update(
                    $user_status,
                    $input->vals(
                        [
                            'id',
                            'name',
                        ],
                        $crush
                    )
                );
            }
            throw $input->exception();
        }

        public static function delete(user_status_model $user_status) {
            return user_status_dao::delete($user_status);
        }

        public static function _validate_insert(input_collection $input) {

            // id
            $input->id->cast('int')->digit(0, 255)->callback(function($id) {
                if (user_status_dao::by_pk($id->val())) {
                    $id->errors('already in use');
                }
            });

            // name
            $input->name->cast('string')->length(1, 255)->callback(function($name) {
                if (user_status_dao::by_name($name->val())) {
                    $name->errors('already in use');
                }
            });
        }

        public static function _validate_update(user_status_model $user_status, input_collection $input) {

            // id
            $input->id->cast('int')->optional()->digit(0, 255)->callback(function($id) use ($user_status) {
                $user_status_info = user_status_dao::by_pk($id->val());
                if ($user_status_info && (int) $user_status_info['id'] !== $user_status->id) {
                    $id->errors('already in use');
                }
            });

            // name
            $input->name->cast('string')->optional()->length(1, 255)->callback(function($name) use ($user_status) {
                $id_arr = user_status_dao::by_name($name->val());
                if (is_array($id_arr) && count($id_arr) && (int) current($id_arr) !== $user_status->id) {
                    $name->errors('already in use');
                }
            });
        }
    }
