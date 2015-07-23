<?php

    namespace Neoform\Acl\Group\Role;

    use Neoform\Input;
    use Neoform\Entity;
    use Neoform;

    class Api {

        /**
         * Give a user access to the following acl groups
         * ACL groups not found in $groups will be removed from this user if they belong to them
         *
         * @param Neoform\Acl\Group\Model     $group
         * @param Neoform\Acl\Role\Collection $roles
         */
        public static function let(Neoform\Acl\Group\Model $group, Neoform\Acl\Role\Collection $roles) {
            $current_role_ids = $group->acl_role_collection()->field('id');
            $role_ids         = $roles->field('id');

            $inserts = [];
            $deletes = [];

            // Insert
            foreach (array_diff($role_ids, $current_role_ids) as $role_id) {
                $inserts[] = [
                    'acl_group_id' => $group->id,
                    'acl_role_id'  => (int) $role_id,
                ];
            }

            if ($inserts) {
                Entity::dao('Neoform\Acl\Group\Role')->insertMulti($inserts);
            }

            // Delete
            foreach (array_diff($current_role_ids, $role_ids) as $role_id) {
                $deletes[] = [
                    'acl_group_id' => $group->id,
                    'acl_role_id'  => (int) $role_id,
                ];
            }

            if ($deletes) {
                Entity::dao('Neoform\Acl\Group\Role')->deleteMulti($deletes);
            }
        }

        /**
         * Creates a Acl Group Role model with $info
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
                return Entity::dao('Neoform\Acl\Group\Role')->insert([
                    'acl_group_id' => $input->acl_group_id->val(),
                    'acl_role_id'  => $input->acl_role_id->val(),
                ]);
            }
            throw $input->exception();
        }

        /**
         * Deletes links
         *
         * @param \Neoform\Acl\Group\Model $acl_group
         * @param \Neoform\Acl\Role\Collection $acl_role_collection
         *
         * @return bool
         */
        public static function delete_by_acl_group(\Neoform\Acl\Group\Model $acl_group, \Neoform\Acl\Role\Collection $acl_role_collection) {
            $keys = [];
            foreach ($acl_role_collection as $acl_role) {
                $keys[] = [
                    'acl_group_id' => (int) $acl_group->id,
                    'acl_role_id'  => (int) $acl_role->id,
                ];
            }
            return Entity::dao('Neoform\Acl\Group\Role')->deleteMulti($keys);
        }

        /**
         * Deletes links
         *
         * @param \Neoform\Acl\Role\Model $acl_role
         * @param \Neoform\Acl\Group\Collection $acl_group_collection
         *
         * @return bool
         */
        public static function delete_by_acl_role(\Neoform\Acl\Role\Model $acl_role, \Neoform\Acl\Group\Collection $acl_group_collection) {
            $keys = [];
            foreach ($acl_group_collection as $acl_group) {
                $keys[] = [
                    'acl_role_id'  => (int) $acl_role->id,
                    'acl_group_id' => (int) $acl_group->id,
                ];
            }
            return Entity::dao('Neoform\Acl\Group\Role')->deleteMulti($keys);
        }

        /**
         * Validates info to for insert
         *
         * @param Input\Collection $input
         */
        public static function _validate_insert(Input\Collection $input) {

            // acl_group_id
            $input->acl_group_id->cast('int')->digit(0, 4294967295)->callback(function($acl_group_id) {
                try {
                    $acl_group_id->data('model', new \Neoform\Acl\Group\Model($acl_group_id->val()));
                } catch (\Neoform\Acl\Group\Exception $e) {
                    $acl_group_id->errors($e->getMessage());
                }
            });

            // acl_role_id
            $input->acl_role_id->cast('int')->digit(0, 4294967295)->callback(function($acl_role_id) {
                try {
                    $acl_role_id->data('model', new \Neoform\Acl\Role\Model($acl_role_id->val()));
                } catch (\Neoform\Acl\Role\Exception $e) {
                    $acl_role_id->errors($e->getMessage());
                }
            });
        }
    }
