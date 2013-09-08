<?php

    namespace neoform;

    class acl_group_user_api {

        public static function insert(array $info) {

            $input = new input_collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {
                return entity::dao('acl_group_user')->insert([
                    'acl_group_id' => $input->acl_group_id->val(),
                    'user_id'      => $input->user_id->val(),
                ]);
            }
            throw $input->exception();
        }

        public static function delete_by_acl_group(acl_group_model $acl_group, user_collection $user_collection) {
            $keys = [];
            foreach ($user_collection as $user) {
                $keys[] = [
                    'acl_group_id' => (int) $acl_group->id,
                    'user_id'      => (int) $user->id,
                ];
            }
            return entity::dao('acl_group_user')->delete_multi($keys);
        }

        public static function delete_by_user(user_model $user, acl_group_collection $acl_group_collection) {
            $keys = [];
            foreach ($acl_group_collection as $acl_group) {
                $keys[] = [
                    'user_id'      => (int) $user->id,
                    'acl_group_id' => (int) $acl_group->id,
                ];
            }
            return entity::dao('acl_group_user')->delete_multi($keys);
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

            // user_id
            $input->user_id->cast('int')->digit(0, 4294967295)->callback(function($user_id){
                try {
                    $user_id->data('model', new user_model($user_id->val()));
                } catch (user_exception $e) {
                    $user_id->errors($e->getMessage());
                }
            });
        }
    }
