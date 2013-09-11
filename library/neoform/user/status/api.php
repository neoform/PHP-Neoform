<?php

    namespace neoform;

    class user_status_api {

        public static function insert(array $info) {

            $input = new input_collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {
                return entity::dao('user_status')->insert([
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
                return entity::dao('user_status')->update(
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
            return entity::dao('user_status')->delete($user_status);
        }

        public static function _validate_insert(input_collection $input) {

            // id
            $input->id->cast('int')->digit(0, 255)->callback(function($id) {
                if (entity::dao('user_status')->record($id->val())) {
                    $id->errors('already in use');
                }
            });

            // name
            $input->name->cast('string')->length(1, 255)->callback(function($name) {
                if (entity::dao('user_status')->by_name($name->val())) {
                    $name->errors('already in use');
                }
            });
        }

        public static function _validate_update(user_status_model $user_status, input_collection $input) {

            // id
            $input->id->cast('int')->optional()->digit(0, 255)->callback(function($id) use ($user_status) {
                $user_status_info = entity::dao('user_status')->record($id->val());
                if ($user_status_info && (int) $user_status_info['id'] !== $user_status->id) {
                    $id->errors('already in use');
                }
            });

            // name
            $input->name->cast('string')->optional()->length(1, 255)->callback(function($name) use ($user_status) {
                $id_arr = entity::dao('user_status')->by_name($name->val());
                if (is_array($id_arr) && $id_arr && (int) current($id_arr) !== $user_status->id) {
                    $name->errors('already in use');
                }
            });
        }
    }