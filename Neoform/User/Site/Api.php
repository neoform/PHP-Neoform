<?php

    namespace Neoform\User\Site;

    use Neoform\Input;
    use Neoform\Entity;
    use Neoform;

    class Api {

        /**
         * Give a user access to the following sites
         * Sites not found in $sites will be removed from this user if they have access
         *
         * @param Neoform\User\Model      $user
         * @param Neoform\Site\Collection $sites
         */
        public static function let(Neoform\User\Model $user, Neoform\Site\Collection $sites) {
            $current_site_ids = $user->site_collection()->field('id');
            $site_ids         = $sites->field('id');

            $inserts = [];
            $deletes = [];

            // Insert
            foreach (array_diff($site_ids, $current_site_ids) as $site_id) {
                $inserts[] = [
                    'user_id' => $user->id,
                    'site_id' => (int) $site_id,
                ];
            }

            if ($inserts) {
                Dao::get()->insertMulti($inserts);
            }

            // Delete
            foreach (array_diff($current_site_ids, $site_ids) as $site_id) {
                $deletes[] = [
                    'user_id' => $user->id,
                    'site_id' => (int) $site_id,
                ];
            }

            if ($deletes) {
                Dao::get()->deleteMulti($deletes);
            }
        }

        /**
         * Creates a User Site model with $info
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
                        'site_id',
                    ])
                );
            }
            throw $input->getException();
        }

        /**
         * Deletes links
         *
         * @param \Neoform\User\Model $user
         * @param \Neoform\Site\Collection $site_collection
         *
         * @return bool
         */
        public static function delete_by_user(\Neoform\User\Model $user, \Neoform\Site\Collection $site_collection) {
            $keys = [];
            foreach ($site_collection as $site) {
                $keys[] = [
                    'user_id' => (int) $user->id,
                    'site_id' => (int) $site->id,
                ];
            }
            return Dao::get()->deleteMulti($keys);
        }

        /**
         * Deletes links
         *
         * @param \Neoform\Site\Model $site
         * @param \Neoform\User\Collection $user_collection
         *
         * @return bool
         */
        public static function delete_by_site(\Neoform\Site\Model $site, \Neoform\User\Collection $user_collection) {
            $keys = [];
            foreach ($user_collection as $user) {
                $keys[] = [
                    'site_id' => (int) $site->id,
                    'user_id' => (int) $user->id,
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

            // site_id
            $input->validate('site_id', 'int')
                ->requireDigit(0, 65535)
                ->callback(function(Input\Input $site_id) {
                    try {
                        $site_id->setData('model', \Neoform\Site\Model::fromPk($site_id->getVal()));
                    } catch (\Neoform\Site\Exception $e) {
                        $site_id->setErrors($e->getMessage());
                    }
                });
        }
    }
