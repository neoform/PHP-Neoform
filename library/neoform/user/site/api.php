<?php

    namespace neoform\user\site;

    use neoform\input;
    use neoform\entity;

    class api {

        public static function insert(array $info) {

            $input = new input\collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {
                return entity::dao('user\site')->insert([
                    'user_id' => $input->user_id->val(),
                    'site_id' => $input->site_id->val(),
                ]);
            }
            throw $input->exception();
        }

        public static function delete_by_user(\neoform\user\model $user, \neoform\site\collection $site_collection) {
            $keys = [];
            foreach ($site_collection as $site) {
                $keys[] = [
                    'user_id' => (int) $user->id,
                    'site_id' => (int) $site->id,
                ];
            }
            return entity::dao('user\site')->delete_multi($keys);
        }

        public static function delete_by_site(\neoform\site\model $site, \neoform\user\collection $user_collection) {
            $keys = [];
            foreach ($user_collection as $user) {
                $keys[] = [
                    'site_id' => (int) $site->id,
                    'user_id' => (int) $user->id,
                ];
            }
            return entity::dao('user\site')->delete_multi($keys);
        }

        public static function _validate_insert(input\collection $input) {

            // user_id
            $input->user_id->cast('int')->digit(0, 4294967295)->callback(function($user_id) {
                try {
                    $user_id->data('model', new \neoform\user\model($user_id->val()));
                } catch (\neoform\user\exception $e) {
                    $user_id->errors($e->getMessage());
                }
            });

            // site_id
            $input->site_id->cast('int')->digit(0, 65535)->callback(function($site_id) {
                try {
                    $site_id->data('model', new \neoform\site\model($site_id->val()));
                } catch (\neoform\site\exception $e) {
                    $site_id->errors($e->getMessage());
                }
            });
        }
    }
