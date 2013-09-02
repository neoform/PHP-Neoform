<?php

    class acl_group_role_api {

        public static function insert(array $info) {

            $input = new input_collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {
                return entity::dao('acl_group_role')->insert([
                    'acl_group_id' => $input->acl_group_id->val(),
                    'acl_role_id'  => $input->acl_role_id->val(),
                ]);
            }
            throw $input->exception();
        }

        public static function delete_by_acl_group(acl_group_model $acl_group, acl_role_collection $acl_role_collection) {
            $keys = [];
            foreach ($acl_role_collection as $acl_role) {
                $keys[] = [
                    'acl_group_id' => (int) $acl_group->id,
                    'acl_role_id'  => (int) $acl_role->id,
                ];
            }
            return entity::dao('acl_group_role')->delete_multi($keys);
        }

        public static function delete_by_acl_role(acl_role_model $acl_role, acl_group_collection $acl_group_collection) {
            $keys = [];
            foreach ($acl_group_collection as $acl_group) {
                $keys[] = [
                    'acl_role_id'  => (int) $acl_role->id,
                    'acl_group_id' => (int) $acl_group->id,
                ];
            }
            return entity::dao('acl_group_role')->delete_multi($keys);
        }

        public static function _validate_insert(input_collection $input) {

            // acl_group_id
            $input->acl_group_id->cast('int')->digit(0, 4294967295)->callback(function($acl_group_id){
                try {
                    $acl_group_id->data('model', new acl_group_model($acl_group_id->val()));
                } catch (acl_group_exception $e) {
                    $acl_group_id->errors($e->getMessage());
                }
            });

            // acl_role_id
            $input->acl_role_id->cast('int')->digit(0, 4294967295)->callback(function($acl_role_id){
                try {
                    $acl_role_id->data('model', new acl_role_model($acl_role_id->val()));
                } catch (acl_role_exception $e) {
                    $acl_role_id->errors($e->getMessage());
                }
            });
        }
    }
