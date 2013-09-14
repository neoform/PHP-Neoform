<?php

    namespace neoform\acl\group\role;

    use neoform\input;
    use neoform\entity;

    class api {

        /**
         * Creates a Acl Group Role model with $info
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
                return entity::dao('acl\group\role')->insert([
                    'acl_group_id' => $input->acl_group_id->val(),
                    'acl_role_id'  => $input->acl_role_id->val(),
                ]);
            }
            throw $input->exception();
        }

        /**
         * Deletes links
         *
         * @param \neoform\acl\group\model $acl_group
         * @param \neoform\acl\role\collection $acl_role_collection
         *
         * @return bool
         */
        public static function delete_by_acl_group(\neoform\acl\group\model $acl_group, \neoform\acl\role\collection $acl_role_collection) {
            $keys = [];
            foreach ($acl_role_collection as $acl_role) {
                $keys[] = [
                    'acl_group_id' => (int) $acl_group->id,
                    'acl_role_id'  => (int) $acl_role->id,
                ];
            }
            return entity::dao('acl\group\role')->delete_multi($keys);
        }

        /**
         * Deletes links
         *
         * @param \neoform\acl\role\model $acl_role
         * @param \neoform\acl\group\collection $acl_group_collection
         *
         * @return bool
         */
        public static function delete_by_acl_role(\neoform\acl\role\model $acl_role, \neoform\acl\group\collection $acl_group_collection) {
            $keys = [];
            foreach ($acl_group_collection as $acl_group) {
                $keys[] = [
                    'acl_role_id'  => (int) $acl_role->id,
                    'acl_group_id' => (int) $acl_group->id,
                ];
            }
            return entity::dao('acl\group\role')->delete_multi($keys);
        }

        /**
         * Validates info to for insert
         *
         * @param input\collection $input
         */
        public static function _validate_insert(input\collection $input) {

            // acl_group_id
            $input->acl_group_id->cast('int')->digit(0, 4294967295)->callback(function($acl_group_id) {
                try {
                    $acl_group_id->data('model', new \neoform\acl\group\model($acl_group_id->val()));
                } catch (\neoform\acl\group\exception $e) {
                    $acl_group_id->errors($e->getMessage());
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
