<?php

    namespace neoform;

    class user_site_api {

        public static function insert(array $info) {

            $input = new input_collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {
                return entity::dao('user_site')->insert([
                    'user_id' => $input->user_id->val(),
                    'site_id' => $input->site_id->val(),
                ]);
            }
            throw $input->exception();
        }

        public static function delete_by_site(site_model $site, user_collection $user_collection) {
            $keys = [];
            foreach ($user_collection as $user) {
                $keys[] = [
                    'site_id' => (int) $site->id,
                    'user_id' => (int) $user->id,
                ];
            }
            return entity::dao('user_site')->delete_multi($keys);
        }

        public static function delete_by_user(user_model $user, site_collection $site_collection) {
            $keys = [];
            foreach ($site_collection as $site) {
                $keys[] = [
                    'user_id' => (int) $user->id,
                    'site_id' => (int) $site->id,
                ];
            }
            return entity::dao('user_site')->delete_multi($keys);
        }

        public static function _validate_insert(input_collection $input) {

            // user_id
            $input->user_id->cast('int')->digit(0, 4294967295)->callback(function($user_id){
                try {
                    $user_id->data('model', new user_model($user_id->val()));
                } catch (user_exception $e) {
                    $user_id->errors($e->getMessage());
                }
            });

            // site_id
            $input->site_id->cast('int')->digit(0, 65535)->callback(function($site_id){
                try {
                    $site_id->data('model', new site_model($site_id->val()));
                } catch (site_exception $e) {
                    $site_id->errors($e->getMessage());
                }
            });
        }
    }
