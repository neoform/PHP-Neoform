<?php

    namespace neoform;

    class user_hashmethod_api {

        public static function insert(array $info) {

            $input = new input_collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {
                return entity::dao('user_hashmethod')->insert([
                    'id'   => $input->id->val(),
                    'name' => $input->name->val(),
                ]);
            }
            throw $input->exception();
        }

        public static function update(user_hashmethod_model $user_hashmethod, array $info, $crush=false) {

            $input = new input_collection($info);

            self::_validate_update($user_hashmethod, $input);

            if ($input->is_valid()) {
                return entity::dao('user_hashmethod')->update(
                    $user_hashmethod,
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

        public static function delete(user_hashmethod_model $user_hashmethod) {
            return entity::dao('user_hashmethod')->delete($user_hashmethod);
        }

        public static function _validate_insert(input_collection $input) {

            // id
            $input->id->cast('int')->digit(0, 255)->callback(function($id) {
                if (entity::dao('user_hashmethod')->record($id->val())) {
                    $id->errors('already in use');
                }
            });

            // name
            $input->name->cast('string')->length(1, 255)->callback(function($name) {
                if (entity::dao('user_hashmethod')->by_name($name->val())) {
                    $name->errors('already in use');
                }
            });
        }

        public static function _validate_update(user_hashmethod_model $user_hashmethod, input_collection $input) {

            // id
            $input->id->cast('int')->optional()->digit(0, 255)->callback(function($id) use ($user_hashmethod) {
                $user_hashmethod_info = entity::dao('user_hashmethod')->record($id->val());
                if ($user_hashmethod_info && (int) $user_hashmethod_info['id'] !== $user_hashmethod->id) {
                    $id->errors('already in use');
                }
            });

            // name
            $input->name->cast('string')->optional()->length(1, 255)->callback(function($name) use ($user_hashmethod) {
                $id_arr = entity::dao('user_hashmethod')->by_name($name->val());
                if (is_array($id_arr) && $id_arr && (int) current($id_arr) !== $user_hashmethod->id) {
                    $name->errors('already in use');
                }
            });
        }
    }
