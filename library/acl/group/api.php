<?php

    class acl_group_api {

        public static function insert(array $info) {

            $input = new input_collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {
                return entity::dao('acl_group')->insert([
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
                return entity::dao('acl_group')->update(
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
            return entity::dao('acl_group')->delete($acl_group);
        }

        public static function _validate_insert(input_collection $input) {

            // id
            $input->id->cast('int')->digit(0, 4294967295)->callback(function($id) {
                if (entity::dao('acl_group')->record($id->val())) {
                    $id->errors('already in use');
                }
            });

            // name
            $input->name->cast('string')->length(1, 64)->callback(function($name) {
                if (entity::dao('acl_group')->by_name($name->val())) {
                    $name->errors('already in use');
                }
            });
        }

        public static function _validate_update(acl_group_model $acl_group, input_collection $input) {

            // id
            $input->id->cast('int')->digit(0, 4294967295)->callback(function($id) use ($acl_group) {
                $acl_group_info = entity::dao('acl_group')->record($id->val());
                if ($acl_group_info && (int) $acl_group_info['id'] !== $acl_group->id) {
                    $id->errors('already in use');
                }
            });

            // name
            $input->name->cast('string')->length(1, 64)->callback(function($name) use ($acl_group) {
                $id_arr = entity::dao('acl_group')->by_name($name->val());
                if (is_array($id_arr) && $id_arr && (int) current($id_arr) !== $acl_group->id) {
                    $name->errors('already in use');
                }
            });
        }
    }
