<?php

    namespace neoform\locale\key;

    use neoform\input;
    use neoform\entity;

    class api {

        public static function insert(array $info) {

            $input = new input\collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {
                $locale_key = entity::dao('neoform\locale\key')->insert([
                    'body'         => $input->body->val(),
                    'locale'       => $input->locale->val(),
                    'namespace_id' => $input->namespace_id->val(),
                ]);
                \neoform\locale\lib::flush_by_locale_namespace($locale_key->locale, $locale_key->locale_namespace());
                return $locale_key;
            }
            throw $input->exception();
        }

        public static function update(model $locale_key, array $info, $crush=false) {

            $input = new input\collection($info);

            self::_validate_update($locale_key, $input);

            if ($input->is_valid()) {
                $updated_locale_key = entity::dao('neoform\locale\key')->update(
                    $locale_key,
                    $input->vals(
                        [
                            'body',
                            'locale',
                            'namespace_id',
                        ],
                        $crush
                    )
                );

                \neoform\locale\lib::flush_by_locale_namespace($locale_key->locale, $locale_key->locale_namespace());
                \neoform\locale\lib::flush_by_locale_namespace($updated_locale_key->locale, $updated_locale_key->locale_namespace());

                return $updated_locale_key;
            }
            throw $input->exception();
        }

        public static function delete(model $locale_key) {
            return entity::dao('neoform\locale\key')->delete($locale_key);
        }

        public static function _validate_insert(input\collection $input) {

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

            // namespace_id
            $input->namespace_id->cast('int')->digit(0, 4294967295)->callback(function($namespace_id) {
                try {
                    $namespace_id->data('model', new \neoform\locale\npace\model($namespace_id->val()));
                } catch (\neoform\locale\npace\exception $e) {
                    $namespace_id->errors($e->getMessage());
                }
            });
        }

        public static function _validate_update(model $locale_key, input\collection $input) {

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

            // namespace_id
            $input->namespace_id->cast('int')->optional()->digit(0, 4294967295)->callback(function($namespace_id) {
                try {
                    $namespace_id->data('model', new \neoform\locale\npace\model($namespace_id->val()));
                } catch (\neoform\locale\npace\exception $e) {
                    $namespace_id->errors($e->getMessage());
                }
            });
        }
    }
