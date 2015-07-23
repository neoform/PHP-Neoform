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
                Entity::dao('Neoform\User\Site')->insertMulti($inserts);
            }

            // Delete
            foreach (array_diff($current_site_ids, $site_ids) as $site_id) {
                $deletes[] = [
                    'user_id' => $user->id,
                    'site_id' => (int) $site_id,
                ];
            }

            if ($deletes) {
                Entity::dao('Neoform\User\Site')->deleteMulti($deletes);
            }
        }

        /**
         * Creates a User Site model with $info
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
                return Entity::dao('Neoform\User\Site')->insert([
                    'user_id' => $input->user_id->val(),
                    'site_id' => $input->site_id->val(),
                ]);
            }
            throw $input->exception();
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
            return Entity::dao('Neoform\User\Site')->deleteMulti($keys);
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
            return Entity::dao('Neoform\User\Site')->deleteMulti($keys);
        }

        /**
         * Validates info to for insert
         *
         * @param Input\Collection $input
         */
        public static function _validate_insert(Input\Collection $input) {

            // user_id
            $input->user_id->cast('int')->digit(0, 4294967295)->callback(function($user_id) {
                try {
                    $user_id->data('model', new \Neoform\User\Model($user_id->val()));
                } catch (\Neoform\User\Exception $e) {
                    $user_id->errors($e->getMessage());
                }
            });

            // site_id
            $input->site_id->cast('int')->digit(0, 65535)->callback(function($site_id) {
                try {
                    $site_id->data('model', new \Neoform\Site\Model($site_id->val()));
                } catch (\Neoform\Site\Exception $e) {
                    $site_id->errors($e->getMessage());
                }
            });
        }
    }
