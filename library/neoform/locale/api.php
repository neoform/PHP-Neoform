<?php

    namespace neoform\locale;

    use neoform\input;
    use neoform\entity;

    class api {

        /**
         * Creates a Locale model with $info
         *
         * @param array $info
         *
         * @return model
         * @throws input\exception
         */
        public static function insert(array $info) {

            $input = new input\collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {
                return entity::dao('locale')->insert([
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
         * @throws input\exception
         */
        public static function update(model $locale, array $info, $crush=false) {

            $input = new input\collection($info);

            self::_validate_update($locale, $input);

            if ($input->is_valid()) {
                return entity::dao('locale')->update(
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
        public static function delete(model $locale) {
            return entity::dao('locale')->delete($locale);
        }

        /**
         * Validates info to for insert
         *
         * @param input\collection $input
         */
        public static function _validate_insert(input\collection $input) {

            // iso2
            $input->iso2->cast('string')->length(1, 2)->callback(function($iso2) {
                if (entity::dao('locale')->record($iso2->val())) {
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
         * @param input\collection $input
         */
        public static function _validate_update(model $locale, input\collection $input) {

            // iso2
            $input->iso2->cast('string')->optional()->length(1, 2)->callback(function($iso2) use ($locale) {
                $locale_info = entity::dao('locale')->record($iso2->val());
                if ($locale_info && (string) $locale_info['iso2'] !== $locale->iso2) {
                    $iso2->errors('already in use');
                }
            });

            // name
            $input->name->cast('string')->optional()->length(1, 255);
        }
    }
