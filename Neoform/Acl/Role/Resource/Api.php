<?php

    namespace Neoform\Acl\Role\Resource;

    use Neoform\Input;
    use Neoform\Entity;
    use Neoform;

    class Api {

        /**
         * Give a role access to the following acl resources
         * ACL resources not found in $resources will be removed from this role if they belong to them
         *
         * @param Neoform\Acl\Role\Model          $role
         * @param Neoform\Acl\Resource\Collection $resources
         */
        public static function let(Neoform\Acl\Role\Model $role, Neoform\Acl\Resource\Collection $resources) {
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
                Entity::dao('Neoform\Acl\Role\Resource')->insertMulti($inserts);
            }

            // Delete
            foreach (array_diff($current_resource_ids, $resource_ids) as $resource_id) {
                $deletes[] = [
                    'acl_role_id'     => $role->id,
                    'acl_resource_id' => (int) $resource_id,
                ];
            }

            if ($deletes) {
                Entity::dao('Neoform\Acl\Role\Resource')->deleteMulti($deletes);
            }
        }

        /**
         * Give a resource access to the following acl roles
         * ACL roles not found in $roles will be removed from this resource if they belong to them
         *
         * @param Neoform\Acl\Resource\Model  $resource
         * @param Neoform\Acl\Role\Collection $roles
         */
        public static function let_resource(Neoform\Acl\Resource\Model $resource, Neoform\Acl\Role\Collection $roles) {
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
                Entity::dao('Neoform\Acl\Role\Resource')->insertMulti($inserts);
            }

            // Delete
            foreach (array_diff($current_role_ids, $role_ids) as $role_id) {
                $deletes[] = [
                    'acl_resource_id' => $resource->id,
                    'acl_role_id'     => (int) $role_id,
                ];
            }

            if ($deletes) {
                Entity::dao('Neoform\Acl\Role\Resource')->deleteMulti($deletes);
            }
        }

        /**
         * Creates a Acl Role Resource model with $info
         *
         * @param array $info
         *
         * @return model
         * @throws Input\Exception
         */
        public static function insert(array $info) {

            $input = new Input\Collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {
                return Entity::dao('Neoform\Acl\Role\Resource')->insert([
                    'acl_role_id'     => $input->acl_role_id->val(),
                    'acl_resource_id' => $input->acl_resource_id->val(),
                ]);
            }
            throw $input->exception();
        }

        /**
         * Deletes links
         *
         * @param \Neoform\Acl\Role\Model $acl_role
         * @param \Neoform\Acl\Resource\Collection $acl_resource_collection
         *
         * @return bool
         */
        public static function delete_by_acl_role(\Neoform\Acl\Role\Model $acl_role, \Neoform\Acl\Resource\Collection $acl_resource_collection) {
            $keys = [];
            foreach ($acl_resource_collection as $acl_resource) {
                $keys[] = [
                    'acl_role_id'     => (int) $acl_role->id,
                    'acl_resource_id' => (int) $acl_resource->id,
                ];
            }
            return Entity::dao('Neoform\Acl\Role\Resource')->deleteMulti($keys);
        }

        /**
         * Deletes links
         *
         * @param \Neoform\Acl\Resource\Model $acl_resource
         * @param \Neoform\Acl\Role\Collection $acl_role_collection
         *
         * @return bool
         */
        public static function delete_by_acl_resource(\Neoform\Acl\Resource\Model $acl_resource, \Neoform\Acl\Role\Collection $acl_role_collection) {
            $keys = [];
            foreach ($acl_role_collection as $acl_role) {
                $keys[] = [
                    'acl_resource_id' => (int) $acl_resource->id,
                    'acl_role_id'     => (int) $acl_role->id,
                ];
            }
            return Entity::dao('Neoform\Acl\Role\Resource')->deleteMulti($keys);
        }

        /**
         * Validates info to for insert
         *
         * @param Input\Collection $input
         */
        public static function _validate_insert(Input\Collection $input) {

            // acl_role_id
            $input->acl_role_id->cast('int')->digit(0, 4294967295)->callback(function($acl_role_id) {
                try {
                    $acl_role_id->data('model', new \Neoform\Acl\Role\Model($acl_role_id->val()));
                } catch (\Neoform\Acl\Role\Exception $e) {
                    $acl_role_id->errors($e->getMessage());
                }
            });

            // acl_resource_id
            $input->acl_resource_id->cast('int')->digit(0, 4294967295)->callback(function($acl_resource_id) {
                try {
                    $acl_resource_id->data('model', new \Neoform\Acl\Resource\Model($acl_resource_id->val()));
                } catch (\Neoform\Acl\Resource\Exception $e) {
                    $acl_resource_id->errors($e->getMessage());
                }
            });
        }
    }
