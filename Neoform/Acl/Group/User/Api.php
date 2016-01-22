<?php

    namespace Neoform\Acl\Group\User;

    use Neoform\Input;
    use Neoform\Entity;
    use Neoform;

    class Api {

        /**
         * Give a user access to the following acl groups
         * ACL groups not found in $groups will be removed from this user if they belong to them
         *
         * @param Neoform\User\Model           $user
         * @param Neoform\Acl\Group\Collection $groups
         */
        public static function let(Neoform\User\Model $user, Neoform\Acl\Group\Collection $groups) {
            $current_group_ids = $user->acl_group_collection()->field('id');
            $group_ids         = $groups->field('id');

            $inserts = [];
            $deletes = [];

            // Insert
            foreach (array_diff($group_ids, $current_group_ids) as $group_id) {
                $inserts[] = [
                    'user_id'      => $user->id,
                    'acl_group_id' => (int) $group_id,
                ];
            }

            if ($inserts) {
                Dao::get()->insertMulti($inserts);
            }

            // Delete
            foreach (array_diff($current_group_ids, $group_ids) as $group_id) {
                $deletes[] = [
                    'user_id'      => $user->id,
                    'acl_group_id' => (int) $group_id,
                ];
            }

            if ($deletes) {
                Dao::get()->deleteMulti($deletes);
            }
        }

        /**
         * Creates a Acl Group User model with $info
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
                        'user_id',
                    ])
                );
            }
            throw $input->getException();
        }

        /**
         * Deletes links
         *
         * @param \Neoform\Acl\Group\Model $acl_group
         * @param \Neoform\User\Collection $user_collection
         *
         * @return bool
         */
        public static function delete_by_acl_group(\Neoform\Acl\Group\Model $acl_group, \Neoform\User\Collection $user_collection) {
            $keys = [];
            foreach ($user_collection as $user) {
                $keys[] = [
                    'acl_group_id' => (int) $acl_group->id,
                    'user_id'      => (int) $user->id,
                ];
            }
            return Dao::get()->deleteMulti($keys);
        }

        /**
         * Deletes links
         *
         * @param \Neoform\User\Model $user
         * @param \Neoform\Acl\Group\Collection $acl_group_collection
         *
         * @return bool
         */
        public static function delete_by_user(\Neoform\User\Model $user, \Neoform\Acl\Group\Collection $acl_group_collection) {
            $keys = [];
            foreach ($acl_group_collection as $acl_group) {
                $keys[] = [
                    'user_id'      => (int) $user->id,
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
        }
    }
