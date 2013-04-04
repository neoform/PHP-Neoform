<?php

    class permission_api {

        public static function insert(array $info) {

            $input = new input_collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {
                return permission_dao::insert([
                    'id'   => $input->id->val(),
                    'name' => $input->name->val(),
                ]);
            }
            throw $input->exception();
        }

        public static function update(permission_model $permission, array $info, $crush=false) {

            $input = new input_collection($info);

            self::_validate_update($permission, $input);

            if ($input->is_valid()) {
                return permission_dao::update(
                    $permission,
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

        public static function _validate_insert(input_collection $input) {

            // id
            $input->id->cast('int')->digit(0, 255);

            // name
            $input->name->cast('string')->length(1, 32)->callback(function($name) {
                $id_arr = permission_dao::by_name($name->val());
                if (is_array($id_arr) && count($id_arr)) {
                    $name->errors('already in use');
                }
            });
        }

        public static function _validate_update(permission_model $permission, input_collection $input) {

            // id
            $input->id->cast('int')->optional()->digit(0, 255);

            // name
            $input->name->cast('string')->optional()->length(1, 32)->callback(function($name) use ($permission) {
                $id_arr = permission_dao::by_name($name->val());
                if (is_array($id_arr) && count($id_arr) && (int) current($id_arr) !== $permission->id) {
                    $name->errors('already in use');
                }
            });
        }

    }
