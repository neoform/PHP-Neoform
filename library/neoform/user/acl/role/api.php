<?php

    namespace neoform\user\acl\role;

    use neoform\input;
    use neoform\entity;
    use neoform;

    class api {

        /**
         * Give a user access to the following acl roles
         * ACL roles not found in $roles will be removed from this user if they have access
         *
         * @param neoform\user\model          $user
         * @param neoform\acl\role\collection $roles
         */
        public static function let(neoform\user\model $user, neoform\acl\role\collection $roles) {
            $current_role_ids = $user->acl_role_collection()->field('id');
            $role_ids          = $roles->field('id');

            $inserts = [];
            $deletes = [];

            // Insert
            foreach (array_diff($role_ids, $current_role_ids) as $role_id) {
                $inserts[] = [
                    'user_id'     => $user->id,
                    'acl_role_id' => (int) $role_id,
                ];
            }

            if ($inserts) {
                entity::dao('user\acl\role')->insert_multi($inserts);
            }

            // Delete
            foreach (array_diff($current_role_ids, $role_ids) as $role_id) {
                $deletes[] = [
                    'user_id'     => $user->id,
                    'acl_role_id' => (int) $role_id,
                ];
            }

            if ($deletes) {
                entity::dao('user\acl\role')->delete_multi($deletes);
            }
        }

        /**
         * Creates a User Acl Role model with $info
         *
         * @param array $info
         *
         * @return model
         * @throws input\exception
         */
        public static function insert(array $info) {

            $input = new input\collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {
                return entity::dao('user\acl\role')->insert([
                    'user_id'     => $input->user_id->val(),
                    'acl_role_id' => $input->acl_role_id->val(),
                ]);
            }
            throw $input->exception();
        }

        /**
         * Deletes links
         *
         * @param \neoform\user\model $user
         * @param \neoform\acl\role\collection $acl_role_collection
         *
         * @return bool
         */
        public static function delete_by_user(\neoform\user\model $user, \neoform\acl\role\collection $acl_role_collection) {
            $keys = [];
            foreach ($acl_role_collection as $acl_role) {
                $keys[] = [
                    'user_id'     => (int) $user->id,
                    'acl_role_id' => (int) $acl_role->id,
                ];
            }
            return entity::dao('user\acl\role')->delete_multi($keys);
        }

        /**
         * Deletes links
         *
         * @param \neoform\acl\role\model $acl_role
         * @param \neoform\user\collection $user_collection
         *
         * @return bool
         */
        public static function delete_by_acl_role(\neoform\acl\role\model $acl_role, \neoform\user\collection $user_collection) {
            $keys = [];
            foreach ($user_collection as $user) {
                $keys[] = [
                    'acl_role_id' => (int) $acl_role->id,
                    'user_id'     => (int) $user->id,
                ];
            }
            return entity::dao('user\acl\role')->delete_multi($keys);
        }

        /**
         * Validates info to for insert
         *
         * @param input\collection $input
         */
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
