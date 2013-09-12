<?php

    namespace neoform\user\acl\role;

    use neoform\input;
    use neoform\entity;

    class api {

        public static function insert(array $info) {

            $input = new input\collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {
                return entity::dao('neoform\user\acl\role')->insert([
                    'user_id'     => $input->user_id->val(),
                    'acl_role_id' => $input->acl_role_id->val(),
                ]);
            }
            throw $input->exception();
        }

        public static function delete_by_user(\neoform\user\model $user, \neoform\acl\role\collection $acl_role_collection) {
            $keys = [];
            foreach ($acl_role_collection as $acl_role) {
                $keys[] = [
                    'user_id'     => (int) $user->id,
                    'acl_role_id' => (int) $acl_role->id,
                ];
            }
            return entity::dao('neoform\user\acl\role')->delete_multi($keys);
        }

        public static function delete_by_acl_role(\neoform\acl\role\model $acl_role, \neoform\user\collection $user_collection) {
            $keys = [];
            foreach ($user_collection as $user) {
                $keys[] = [
                    'acl_role_id' => (int) $acl_role->id,
                    'user_id'     => (int) $user->id,
                ];
            }
            return entity::dao('neoform\user\acl\role')->delete_multi($keys);
        }

        public static function _validate_insert(input\collection $input) {

            // user_id
            $input->user_id->cast('int')->digit(0, 4294967295)->callback(function($user_id) {
                try {
                    $user_id->data('model', new \neoform\user\model($user_id->val()));
                } catch (\neoform\user\exception $e) {
                    $user_id->errors($e->getMessage());
                }
            });

            // acl_role_id
            $input->acl_role_id->cast('int')->digit(0, 4294967295)->callback(function($acl_role_id) {
                try {
                    $acl_role_id->data('model', new \neoform\acl\role\model($acl_role_id->val()));
                } catch (\neoform\acl\role\exception $e) {
                    $acl_role_id->errors($e->getMessage());
                }
            });
        }
    }
