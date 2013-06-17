<?php

    class user_acl_role_api {

        public static function insert(array $info) {

            $input = new input_collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {
                return user_acl_role_dao::insert([
                    'user_id' => $input->user_id->val(),
                    'role_id' => $input->role_id->val(),
                ]);
            }
            throw $input->exception();
        }

        public static function delete_by_user(user_model $user, acl_role_collection $acl_role_collection) {
            $keys = [];
            foreach ($acl_role_collection as $acl_role) {
                $keys[] = [
                    'user_id' => (int) $user->id,
                    'role_id' => (int) $acl_role->id,
                ];
            }
            return user_acl_role_dao::deletes($keys);
        }

        public static function delete_by_acl_role(acl_role_model $acl_role, user_collection $user_collection) {
            $keys = [];
            foreach ($user_collection as $user) {
                $keys[] = [
                    'role_id' => (int) $acl_role->id,
                    'user_id' => (int) $user->id,
                ];
            }
            return user_acl_role_dao::deletes($keys);
        }

        public static function _validate_insert(input_collection $input) {

            // user_id
            $input->user_id->cast('int')->digit(0, 4294967295)->callback(function($user_id){
                try {
                    $user_id->data('model', new user_model($user_id->val()));
                } catch (user_exception $e) {
                    $user_id->errors($e->getMessage());
                }
            });

            // role_id
            $input->role_id->cast('int')->digit(0, 4294967295)->callback(function($role_id){
                try {
                    $role_id->data('model', new acl_role_model($role_id->val()));
                } catch (acl_role_exception $e) {
                    $role_id->errors($e->getMessage());
                }
            });
        }
    }
