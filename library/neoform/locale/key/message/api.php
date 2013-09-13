<?php

    namespace neoform\locale\key\message;

    use neoform\input;
    use neoform\entity;

    class api {

        public static function insert(array $info) {

            $input = new input\collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {
                return entity::dao('locale\key\message')->insert([
                    'key_id' => $input->key_id->val(),
                    'body'   => $input->body->val(),
                    'locale' => $input->locale->val(),
                ]);
            }
            throw $input->exception();
        }

        public static function update(model $locale_key_message, array $info, $crush=false) {

            $input = new input\collection($info);

            self::_validate_update($locale_key_message, $input);

            if ($input->is_valid()) {
                return entity::dao('locale\key\message')->update(
                    $locale_key_message,
                    $input->vals(
                        [
                            'key_id',
                            'body',
                            'locale',
                        ],
                        $crush
                    )
                );
            }
            throw $input->exception();
        }

        public static function delete(model $locale_key_message) {
            return entity::dao('locale\key\message')->delete($locale_key_message);
        }

        public static function _validate_insert(input\collection $input) {

            // key_id
            $input->key_id->cast('int')->digit(0, 4294967295)->callback(function($key_id) {
                try {
                    $key_id->data('model', new \neoform\locale\key\model($key_id->val()));
                } catch (\neoform\locale\key\exception $e) {
                    $key_id->errors($e->getMessage());
                }
            });

            // body
            $input->body->cast('string')->length(1, 255);

            // locale
            $input->locale->cast('string')->length(1, 2)->callback(function($locale) {
                try {
                    $locale->data('model', new \neoform\locale\model($locale->val()));
                } catch (\neoform\locale\exception $e) {
                    $locale->errors($e->getMessage());
                }
            });
        }

        public static function _validate_update(model $locale_key_message, input\collection $input) {

            // key_id
            $input->key_id->cast('int')->optional()->digit(0, 4294967295)->callback(function($key_id) {
                try {
                    $key_id->data('model', new \neoform\locale\key\model($key_id->val()));
                } catch (\neoform\locale\key\exception $e) {
                    $key_id->errors($e->getMessage());
                }
            });

            // body
            $input->body->cast('string')->optional()->length(1, 255);

            // locale
            $input->locale->cast('string')->optional()->length(1, 2)->callback(function($locale) {
                try {
                    $locale->data('model', new \neoform\locale\model($locale->val()));
                } catch (\neoform\locale\exception $e) {
                    $locale->errors($e->getMessage());
                }
            });
        }
    }
