<?php

    namespace Neoform\Locale\Key;

    use Neoform\Input;
    use Neoform\Entity;

    class Api {

        /**
         * Creates a Locale Key model with $info
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
                $locale_key = Entity::dao('Neoform\Locale\Key')->insert([
                    'body'         => $input->body->val(),
                    'locale'       => $input->locale->val(),
                    'namespace_id' => $input->namespace_id->val(),
                ]);
                \Neoform\Locale\Lib::flushByLocaleNamespace($locale_key->locale, $locale_key->locale_namespace());
                return $locale_key;
            }
            throw $input->exception();
        }

        /**
         * Update a Locale Key model with $info
         *
         * @param model $locale_key
         * @param array $info
         * @param bool  $crush
         *
         * @return model
         * @throws Input\Exception
         */
        public static function update(Model $locale_key, array $info, $crush=false) {

            $input = new Input\Collection($info);

            self::_validate_update($locale_key, $input);

            if ($input->is_valid()) {
                $updated_locale_key = Entity::dao('Neoform\Locale\Key')->update(
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

                \Neoform\Locale\Lib::flushByLocaleNamespace($locale_key->locale, $locale_key->locale_namespace());
                \Neoform\Locale\Lib::flushByLocaleNamespace($updated_locale_key->locale, $updated_locale_key->locale_namespace());

                return $updated_locale_key;
            }
            throw $input->exception();
        }

        /**
         * Delete a Locale Key
         *
         * @param model $locale_key
         *
         * @return bool
         */
        public static function delete(Model $locale_key) {
            return Entity::dao('Neoform\Locale\Key')->delete($locale_key);
        }

        /**
         * Validates info to for insert
         *
         * @param Input\Collection $input
         */
        public static function _validate_insert(Input\Collection $input) {

            // body
            $input->body->cast('string')->length(1, 255);

            // locale
            $input->locale->cast('string')->length(1, 2)->callback(function($locale) {
                try {
                    $locale->data('model', new \Neoform\Locale\Model($locale->val()));
                } catch (\Neoform\Locale\Exception $e) {
                    $locale->errors($e->getMessage());
                }
            });

            // namespace_id
            $input->namespace_id->cast('int')->digit(0, 4294967295)->callback(function($namespace_id) {
                try {
                    $namespace_id->data('model', new \Neoform\Locale\Nspace\Model($namespace_id->val()));
                } catch (\Neoform\Locale\Nspace\Exception $e) {
                    $namespace_id->errors($e->getMessage());
                }
            });
        }

        /**
         * Validates info to update a Locale Key model
         *
         * @param model $locale_key
         * @param Input\Collection $input
         */
        public static function _validate_update(Model $locale_key, Input\Collection $input) {

            // body
            $input->body->cast('string')->optional()->length(1, 255);

            // locale
            $input->locale->cast('string')->optional()->length(1, 2)->callback(function($locale) {
                try {
                    $locale->data('model', new \Neoform\Locale\Model($locale->val()));
                } catch (\Neoform\Locale\Exception $e) {
                    $locale->errors($e->getMessage());
                }
            });

            // namespace_id
            $input->namespace_id->cast('int')->optional()->digit(0, 4294967295)->callback(function($namespace_id) {
                try {
                    $namespace_id->data('model', new \Neoform\Locale\Nspace\Model($namespace_id->val()));
                } catch (\Neoform\Locale\Nspace\Exception $e) {
                    $namespace_id->errors($e->getMessage());
                }
            });
        }
    }
