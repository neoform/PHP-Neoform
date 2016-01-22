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
                Dao::get()->insertMulti($inserts);
            }

            // Delete
            foreach (array_diff($current_role_ids, $role_ids) as $role_id) {
                $deletes[] = [
                    'acl_group_id' => $group->id,
                    'acl_role_id'  => (int) $role_id,
                ];
            }

            if ($deletes) {
                Dao::get()->deleteMulti($deletes);
            }
        }

        /**
         * Creates a Acl Group Role model with $info
         *
         * @param array $info
         *
         * @return Model
         * @throws Input\Exception
         */
        public static function insert(array $info) {

            $input = new Input\Collection($info);

            self::_validate_insert($input);

            if ($input->isValid()) {
                return Dao::get()->insert(
                    $input->getVals([
                        'acl_group_id',
                        'acl_role_id',
                    ])
                );
            }
            throw $input->getException();
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
            return Dao::get()->deleteMulti($keys);
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
            return Dao::get()->deleteMulti($keys);
        }

        /**
         * Validates info to for insert
         *
         * @param Input\Collection $input
         */
        public static function _validate_insert(Input\Collection $input) {

            // acl_group_id
            $input->validate('acl_group_id', 'int')
                ->requireDigit(0, 4294967295)
                ->callback(function(Input\Input $acl_group_id) {
                    try {
                        $acl_group_id->setData('model', \Neoform\Acl\Group\Model::fromPk($acl_group_id->getVal()));
                    } catch (\Neoform\Acl\Group\Exception $e) {
                        $acl_group_id->setErrors($e->getMessage());
                    }
                });

            // acl_role_id
            $input->validate('acl_role_id', 'int')
                ->requireDigit(0, 4294967295)
                ->callback(function(Input\Input $acl_role_id) {
                    try {
                        $acl_role_id->setData('model', \Neoform\Acl\Role\Model::fromPk($acl_role_id->getVal()));
                    } catch (\Neoform\Acl\Role\Exception $e) {
                        $acl_role_id->setErrors($e->getMessage());
                    }
                });
        }
    }
