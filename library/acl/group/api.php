<?php

    class acl_group_api {

        public static function insert(array $info) {

            $input = new input_collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {
                return acl_group_dao::insert([
                    'id'   => $input->id->val(),
                    'name' => $input->name->val(),
                ]);
            }
            throw $input->exception();
        }

        public static function update(acl_group_model $acl_group, array $info, $crush=false) {

            $input = new input_collection($info);

            self::_validate_update($acl_group, $input);

            if ($input->is_valid()) {
                return acl_group_dao::update(
                    $acl_group,
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

        public static function delete(acl_group_model $acl_group) {
            return acl_group_dao::delete($acl_group);
        }

        public static function _validate_insert(input_collection $input) {

            // id
            $input->id->cast('int')->digit(0, 4294967295)->callback(function($id) {
                $id_arr = acl_group_dao::by_id($id->val());
                if (is_array($id_arr) && count($id_arr)) {
                    $id->errors('already in use');
                }
            });

            // name
            $input->name->cast('string')->length(1, 64)->callback(function($name) {
                $id_arr = acl_group_dao::by_name($name->val());
                if (is_array($id_arr) && count($id_arr)) {
                    $name->errors('already in use');
                }
            });
        }

        public static function _validate_update(acl_group_model $acl_group, input_collection $input) {

            // id
            $input->id->cast('int')->optional()->digit(0, 4294967295)->callback(function($id) use ($acl_group) {
                $id_arr = acl_group_dao::by_id($id->val());
                if (is_array($id_arr) && count($id_arr) && (int) current($id_arr) !== $acl_group->id) {
                    $id->errors('already in use');
                }
            });

            // name
            $input->name->cast('string')->optional()->length(1, 64)->callback(function($name) use ($acl_group) {
                $id_arr = acl_group_dao::by_name($name->val());
                if (is_array($id_arr) && count($id_arr) && (int) current($id_arr) !== $acl_group->id) {
                    $name->errors('already in use');
                }
            });
        }
    }
