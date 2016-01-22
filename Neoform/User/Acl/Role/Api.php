<?php

    namespace Neoform\User\Acl\Role;

    use Neoform\Input;
    use Neoform\Entity;
    use Neoform;

    class Api {

        /**
         * Give a user access to the following acl roles
         * ACL roles not found in $roles will be removed from this user if they have access
         *
         * @param Neoform\User\Model          $user
         * @param Neoform\Acl\Role\Collection $roles
         */
        public static function let(Neoform\User\Model $user, Neoform\Acl\Role\Collection $roles) {
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
                Dao::get()->insertMulti($inserts);
            }

            // Delete
            foreach (array_diff($current_role_ids, $role_ids) as $role_id) {
                $deletes[] = [
                    'user_id'     => $user->id,
                    'acl_role_id' => (int) $role_id,
                ];
            }

            if ($deletes) {
                Dao::get()->deleteMulti($deletes);
            }
        }

        /**
         * Creates a User Acl Role model with $info
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
                        'user_id',
                        'acl_role_id',
                    ])
                );
            }
            throw $input->getException();
        }

        /**
         * Deletes links
         *
         * @param \Neoform\User\Model $user
         * @param \Neoform\Acl\Role\Collection $acl_role_collection
         *
         * @return bool
         */
        public static function delete_by_user(\Neoform\User\Model $user, \Neoform\Acl\Role\Collection $acl_role_collection) {
            $keys = [];
            foreach ($acl_role_collection as $acl_role) {
                $keys[] = [
                    'user_id'     => (int) $user->id,
                    'acl_role_id' => (int) $acl_role->id,
                ];
            }
            return Dao::get()->deleteMulti($keys);
        }

        /**
         * Deletes links
         *
         * @param \Neoform\Acl\Role\Model $acl_role
         * @param \Neoform\User\Collection $user_collection
         *
         * @return bool
         */
        public static function delete_by_acl_role(\Neoform\Acl\Role\Model $acl_role, \Neoform\User\Collection $user_collection) {
            $keys = [];
            foreach ($user_collection as $user) {
                $keys[] = [
                    'acl_role_id' => (int) $acl_role->id,
                    'user_id'     => (int) $user->id,
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

            // user_id
            $input->validate('user_id', 'int')
                ->requireDigit(0, 4294967295)
                ->callback(function(Input\Input $user_id) {
                    try {
                        $user_id->setData('model', \Neoform\User\Model::fromPk($user_id->getVal()));
                    } catch (\Neoform\User\Exception $e) {
                        $user_id->setErrors($e->getMessage());
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
