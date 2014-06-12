<?php

    namespace neoform\acl\role\resource;

    use neoform\input;
    use neoform\entity;
    use neoform;

    class api {

        /**
         * Give a role access to the following acl resources
         * ACL resources not found in $resources will be removed from this role if they belong to them
         *
         * @param neoform\acl\role\model          $role
         * @param neoform\acl\resource\collection $resources
         */
        public static function let(neoform\acl\role\model $role, neoform\acl\resource\collection $resources) {
            $current_resource_ids = $role->acl_resource_collection()->field('id');
            $resource_ids         = $resources->field('id');

            $inserts = [];
            $deletes = [];

            // Insert
            foreach (array_diff($resource_ids, $current_resource_ids) as $resource_id) {
                $inserts[] = [
                    'acl_role_id'     => $role->id,
                    'acl_resource_id' => (int) $resource_id,
                ];
            }

            if ($inserts) {
                entity::dao('acl\role\resource')->insert_multi($inserts);
            }

            // Delete
            foreach (array_diff($current_resource_ids, $resource_ids) as $resource_id) {
                $deletes[] = [
                    'acl_role_id'     => $role->id,
                    'acl_resource_id' => (int) $resource_id,
                ];
            }

            if ($deletes) {
                entity::dao('acl\role\resource')->delete_multi($deletes);
            }
        }

        /**
         * Give a resource access to the following acl roles
         * ACL roles not found in $roles will be removed from this resource if they belong to them
         *
         * @param neoform\acl\resource\model  $resource
         * @param neoform\acl\role\collection $roles
         */
        public static function let_resource(neoform\acl\resource\model $resource, neoform\acl\role\collection $roles) {
            $current_role_ids = $resource->acl_role_collection()->field('id');
            $role_ids         = $roles->field('id');

            $inserts = [];
            $deletes = [];

            // Insert
            foreach (array_diff($role_ids, $current_role_ids) as $role_id) {
                $inserts[] = [
                    'acl_resource_id' => $resource->id,
                    'acl_role_id'     => (int) $role_id,
                ];
            }

            if ($inserts) {
                entity::dao('acl\role\resource')->insert_multi($inserts);
            }

            // Delete
            foreach (array_diff($current_role_ids, $role_ids) as $role_id) {
                $deletes[] = [
                    'acl_resource_id' => $resource->id,
                    'acl_role_id'     => (int) $role_id,
                ];
            }

            if ($deletes) {
                entity::dao('acl\role\resource')->delete_multi($deletes);
            }
        }

        /**
         * Creates a Acl Role Resource model with $info
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
                return entity::dao('acl\role\resource')->insert([
                    'acl_role_id'     => $input->acl_role_id->val(),
                    'acl_resource_id' => $input->acl_resource_id->val(),
                ]);
            }
            throw $input->exception();
        }

        /**
         * Deletes links
         *
         * @param \neoform\acl\role\model $acl_role
         * @param \neoform\acl\resource\collection $acl_resource_collection
         *
         * @return bool
         */
        public static function delete_by_acl_role(\neoform\acl\role\model $acl_role, \neoform\acl\resource\collection $acl_resource_collection) {
            $keys = [];
            foreach ($acl_resource_collection as $acl_resource) {
                $keys[] = [
                    'acl_role_id'     => (int) $acl_role->id,
                    'acl_resource_id' => (int) $acl_resource->id,
                ];
            }
            return entity::dao('acl\role\resource')->delete_multi($keys);
        }

        /**
         * Deletes links
         *
         * @param \neoform\acl\resource\model $acl_resource
         * @param \neoform\acl\role\collection $acl_role_collection
         *
         * @return bool
         */
        public static function delete_by_acl_resource(\neoform\acl\resource\model $acl_resource, \neoform\acl\role\collection $acl_role_collection) {
            $keys = [];
            foreach ($acl_role_collection as $acl_role) {
                $keys[] = [
                    'acl_resource_id' => (int) $acl_resource->id,
                    'acl_role_id'     => (int) $acl_role->id,
                ];
            }
            return entity::dao('acl\role\resource')->delete_multi($keys);
        }

        /**
         * Validates info to for insert
         *
         * @param input\collection $input
         */
        public static function _validate_insert(input\collection $input) {

            // acl_role_id
            $input->acl_role_id->cast('int')->digit(0, 4294967295)->callback(function($acl_role_id) {
                try {
                    $acl_role_id->data('model', new \neoform\acl\role\model($acl_role_id->val()));
                } catch (\neoform\acl\role\exception $e) {
                    $acl_role_id->errors($e->getMessage());
                }
            });

            // acl_resource_id
            $input->acl_resource_id->cast('int')->digit(0, 4294967295)->callback(function($acl_resource_id) {
                try {
                    $acl_resource_id->data('model', new \neoform\acl\resource\model($acl_resource_id->val()));
                } catch (\neoform\acl\resource\exception $e) {
                    $acl_resource_id->errors($e->getMessage());
                }
            });
        }
    }
