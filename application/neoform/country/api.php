<?php

    namespace neoform\country;

    use neoform\input;
    use neoform\entity;

    class api {

        public static function insert(array $info) {

            $input = new input\collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {
                return entity::dao('country')->insert([
                    'name'            => $input->name->val(),
                    'name_normalized' => $input->name_normalized->val(),
                    'iso2'            => $input->iso2->val(),
                    'iso3'            => $input->iso3->val(),
                ]);
            }
            throw $input->exception();
        }

        public static function update(model $country, array $info, $crush=false) {

            $input = new input\collection($info);

            self::_validate_update($country, $input);

            if ($input->is_valid()) {
                return entity::dao('country')->update(
                    $country,
                    $input->vals(
                        [
                            'name',
                            'name_normalized',
                            'iso2',
                            'iso3',
                        ],
                        $crush
                    )
                );
            }
            throw $input->exception();
        }

        public static function delete(model $country) {
            return entity::dao('country')->delete($country);
        }

        public static function _validate_insert(input\collection $input) {

            // name
            $input->name->cast('string')->length(1, 255)->callback(function($name) {
                if (entity::dao('country')->by_name($name->val())) {
                    $name->errors('already in use');
                }
            });

            // name_normalized
            $input->name_normalized->cast('string')->length(1, 255)->callback(function($name_normalized) {
                if (entity::dao('country')->by_name_normalized($name_normalized->val())) {
                    $name_normalized->errors('already in use');
                }
            });

            // iso2
            $input->iso2->cast('string')->length(1, 2)->callback(function($iso2) {
                if (entity::dao('country')->by_iso2($iso2->val())) {
                    $iso2->errors('already in use');
                }
            });

            // iso3
            $input->iso3->cast('string')->length(1, 3)->callback(function($iso3) {
                if (entity::dao('country')->by_iso3($iso3->val())) {
                    $iso3->errors('already in use');
                }
            });
        }

        public static function _validate_update(model $country, input\collection $input) {

            // name
            $input->name->cast('string')->optional()->length(1, 255)->callback(function($name) use ($country) {
                $id_arr = entity::dao('country')->by_name($name->val());
                if (is_array($id_arr) && $id_arr && (int) current($id_arr) !== $country->id) {
                    $name->errors('already in use');
                }
            });

            // name_normalized
            $input->name_normalized->cast('string')->optional()->length(1, 255)->callback(function($name_normalized) use ($country) {
                $id_arr = entity::dao('country')->by_name_normalized($name_normalized->val());
                if (is_array($id_arr) && $id_arr && (int) current($id_arr) !== $country->id) {
                    $name_normalized->errors('already in use');
                }
            });

            // iso2
            $input->iso2->cast('string')->optional()->length(1, 2)->callback(function($iso2) use ($country) {
                $id_arr = entity::dao('country')->by_iso2($iso2->val());
                if (is_array($id_arr) && $id_arr && (int) current($id_arr) !== $country->id) {
                    $iso2->errors('already in use');
                }
            });

            // iso3
            $input->iso3->cast('string')->optional()->length(1, 3)->callback(function($iso3) use ($country) {
                $id_arr = entity::dao('country')->by_iso3($iso3->val());
                if (is_array($id_arr) && $id_arr && (int) current($id_arr) !== $country->id) {
                    $iso3->errors('already in use');
                }
            });
        }
    }
