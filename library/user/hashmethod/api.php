<?php

    class user_hashmethod_api {

        public static function insert(array $info) {

            $input = new input_collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {
                return user_hashmethod_dao::insert([
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
                return user_hashmethod_dao::update(
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
            return user_hashmethod_dao::delete($user_hashmethod);
        }

        public static function _validate_insert(input_collection $input) {

            // id
            $input->id->cast('int')->digit(0, 255);

            // name
            $input->name->cast('string')->length(1, 255)->callback(function($name) {
                $id_arr = user_hashmethod_dao::by_name($name->val());
                if (is_array($id_arr) && count($id_arr)) {
                    $name->errors('already in use');
                }
            });
        }

        public static function _validate_update(user_hashmethod_model $user_hashmethod, input_collection $input) {

            // id
            $input->id->cast('int')->optional()->digit(0, 255);

            // name
            $input->name->cast('string')->optional()->length(1, 255)->callback(function($name) use ($user_hashmethod) {
                $id_arr = user_hashmethod_dao::by_name($name->val());
                if (is_array($id_arr) && count($id_arr) && (int) current($id_arr) !== $user_hashmethod->id) {
                    $name->errors('already in use');
                }
            });
        }

    }
