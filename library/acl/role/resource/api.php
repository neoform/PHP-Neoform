<?php

    class acl_role_resource_api {

        public static function insert(array $info) {

            $input = new input_collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {
                return acl_role_resource_dao::insert([
                    'acl_role_id'     => $input->acl_role_id->val(),
                    'acl_resource_id' => $input->acl_resource_id->val(),
                ]);
            }
            throw $input->exception();
        }

        public static function delete_by_acl_role(acl_role_model $acl_role, acl_resource_collection $acl_resource_collection) {
            $keys = [];
            foreach ($acl_resource_collection as $acl_resource) {
                $keys[] = [
                    'acl_role_id'     => (int) $acl_role->id,
                    'acl_resource_id' => (int) $acl_resource->id,
                ];
            }
            return acl_role_resource_dao::deletes($keys);
        }

        public static function delete_by_acl_resource(acl_resource_model $acl_resource, acl_role_collection $acl_role_collection) {
            $keys = [];
            foreach ($acl_role_collection as $acl_role) {
                $keys[] = [
                    'acl_resource_id' => (int) $acl_resource->id,
                    'acl_role_id'     => (int) $acl_role->id,
                ];
            }
            return acl_role_resource_dao::deletes($keys);
        }

        public static function _validate_insert(input_collection $input) {

            // acl_role_id
            $input->acl_role_id->cast('int')->digit(0, 4294967295)->callback(function($acl_role_id){
                try {
                    $acl_role_id->data('model', new acl_role_model($acl_role_id->val()));
                } catch (acl_role_exception $e) {
                    $acl_role_id->errors($e->getMessage());
                }
            });

            // acl_resource_id
            $input->acl_resource_id->cast('int')->digit(0, 4294967295)->callback(function($acl_resource_id){
                try {
                    $acl_resource_id->data('model', new acl_resource_model($acl_resource_id->val()));
                } catch (acl_resource_exception $e) {
                    $acl_resource_id->errors($e->getMessage());
                }
            });
        }
    }
