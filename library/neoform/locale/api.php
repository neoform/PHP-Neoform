<?php

    namespace neoform;

    class locale_api {

        public static function insert(array $info) {

            $input = new input_collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {
                return entity::dao('locale')->insert([
                    'iso2' => $input->iso2->val(),
                    'name' => $input->name->val(),
                ]);
            }
            throw $input->exception();
        }

        public static function update(locale_model $locale, array $info, $crush=false) {

            $input = new input_collection($info);

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

        public static function delete(locale_model $locale) {
            return entity::dao('locale')->delete($locale);
        }

        public static function _validate_insert(input_collection $input) {

            // iso2
            $input->iso2->cast('string')->length(1, 2);

            // name
            $input->name->cast('string')->length(1, 255);
        }

        public static function _validate_update(locale_model $locale, input_collection $input) {

            // iso2
            $input->iso2->cast('string')->optional()->length(1, 2);

            // name
            $input->name->cast('string')->optional()->length(1, 255);
        }
    }
