<?php

    class user_permission_api {

        public static function insert(array $info) {

            $input = new input_collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {
                return user_permission_dao::insert([
                    'user_id'       => $input->user_id->val(),
                    'permission_id' => $input->permission_id->val(),
                ]);
            }
            throw $input->exception();
        }

        public static function delete_by_permission(permission_model $permission, user_collection $user_collection) {
            $keys = [];
            foreach ($user_collection as $user) {
                $keys[] = [
                    'permission_id' => (int) $permission->id,
                    'user_id'       => (int) $user->id,
                ];
            }
            return user_permission_dao::deletes($keys);
        }

        public static function delete_by_user(user_model $user, permission_collection $permission_collection) {
            $keys = [];
            foreach ($permission_collection as $permission) {
                $keys[] = [
                    'user_id'       => (int) $user->id,
                    'permission_id' => (int) $permission->id,
                ];
            }
            return user_permission_dao::deletes($keys);
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

            // permission_id
            $input->permission_id->cast('int')->digit(0, 255)->callback(function($permission_id){
                try {
                    $permission_id->data('model', new permission_model($permission_id->val()));
                } catch (permission_exception $e) {
                    $permission_id->errors($e->getMessage());
                }
            });
        }

    }
