<?php

    namespace Neoform\Locale;

    use Neoform\Input;
    use Neoform\Entity;

    class Api {

        /**
         * Creates a Locale model with $info
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
                return Entity::dao('Neoform\Locale')->insert([
                    'iso2' => $input->iso2->val(),
                    'name' => $input->name->val(),
                ]);
            }
            throw $input->exception();
        }

        /**
         * Update a Locale model with $info
         *
         * @param model $locale
         * @param array $info
         * @param bool  $crush
         *
         * @return model
         * @throws Input\Exception
         */
        public static function update(Model $locale, array $info, $crush=false) {

            $input = new Input\Collection($info);

            self::_validate_update($locale, $input);

            if ($input->is_valid()) {
                return Entity::dao('Neoform\Locale')->update(
                    $locale,
                    $input->vals(
                        [
                            'iso2',
                            'name',
                        ],
                        $crush
                    )
                );
            }
            throw $input->exception();
        }

        /**
         * Delete a Locale
         *
         * @param model $locale
         *
         * @return bool
         */
        public static function delete(Model $locale) {
            return Entity::dao('Neoform\Locale')->delete($locale);
        }

        /**
         * Validates info to for insert
         *
         * @param Input\Collection $input
         */
        public static function _validate_insert(Input\Collection $input) {

            // iso2
            $input->iso2->cast('string')->length(1, 2)->callback(function($iso2) {
                if (Entity::dao('Neoform\Locale')->record($iso2->val())) {
                    $iso2->errors('already in use');
                }
            });

            // name
            $input->name->cast('string')->length(1, 255);
        }

        /**
         * Validates info to update a Locale model
         *
         * @param model $locale
         * @param Input\Collection $input
         */
        public static function _validate_update(Model $locale, Input\Collection $input) {

            // iso2
            $input->iso2->cast('string')->optional()->length(1, 2)->callback(function($iso2) use ($locale) {
                $locale_info = Entity::dao('Neoform\Locale')->record($iso2->val());
                if ($locale_info && (string) $locale_info['iso2'] !== $locale->iso2) {
                    $iso2->errors('already in use');
                }
            });

            // name
            $input->name->cast('string')->optional()->length(1, 255);
        }
    }
