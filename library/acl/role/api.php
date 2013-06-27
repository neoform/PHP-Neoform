<?php

    class acl_role_api {

        public static function insert(array $info) {

            $input = new input_collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {
                return acl_role_dao::insert([
                    'name' => $input->name->val(),
                ]);
            }
            throw $input->exception();
        }

        public static function update(acl_role_model $acl_role, array $info, $crush=false) {

            $input = new input_collection($info);

            self::_validate_update($acl_role, $input);

            if ($input->is_valid()) {
                return acl_role_dao::update(
                    $acl_role,
                    $input->vals(
                        [
                            'name',
                        ],
                        $crush
                    )
                );
            }
            throw $input->exception();
        }

        public static function delete(acl_role_model $acl_role) {
            return acl_role_dao::delete($acl_role);
        }

        public static function _validate_insert(input_collection $input) {

            // name
            $input->name->cast('string')->length(1, 64)->callback(function($name) {
                if (acl_role_dao::by_name($name->val())) {
                    $name->errors('already in use');
                }
            });
        }

        public static function _validate_update(acl_role_model $acl_role, input_collection $input) {

            // name
            $input->name->cast('string')->optional()->length(1, 64)->callback(function($name) use ($acl_role) {
                $id_arr = acl_role_dao::by_name($name->val());
                if (is_array($id_arr) && count($id_arr) && (int) current($id_arr) !== $acl_role->id) {
                    $name->errors('already in use');
                }
            });
        }
    }
