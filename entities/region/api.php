<?php

    class region_api {

        public static function insert(array $info) {

            $input = new input_collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {
                return region_dao::insert([
                    'country_id'      => $input->country_id->val(),
                    'name'            => $input->name->val(),
                    'name_normalized' => $input->name_normalized->val(),
                    'name_soundex'    => $input->name_soundex->val(),
                    'iso2'            => $input->iso2->val(),
                    'longitude'       => $input->longitude->val(),
                    'latitude'        => $input->latitude->val(),
                ]);
            }
            throw $input->exception();
        }

        public static function update(region_model $region, array $info, $crush=false) {

            $input = new input_collection($info);

            self::_validate_update($region, $input);

            if ($input->is_valid()) {
                return region_dao::update(
                    $region,
                    $input->vals(
                        [
                            'country_id',
                            'name',
                            'name_normalized',
                            'name_soundex',
                            'iso2',
                            'longitude',
                            'latitude',
                        ],
                        $crush
                    )
                );
            }
            throw $input->exception();
        }

        public static function delete(region_model $region) {
            return region_dao::delete($region);
        }

        public static function _validate_insert(input_collection $input) {

            // country_id
            $input->country_id->cast('int')->digit(0, 255)->callback(function($country_id) {
                $id_arr = region_dao::by_country($country_id->val());
                if (is_array($id_arr) && count($id_arr)) {
                    $country_id->errors('already in use');
                }
            })->callback(function($country_id) {
                $id_arr = region_dao::by_country($country_id->val());
                if (is_array($id_arr) && count($id_arr)) {
                    $country_id->errors('already in use');
                }
            })->callback(function($country_id){
                try {
                    $country_id->data('model', new country_model($country_id->val()));
                } catch (country_exception $e) {
                    $country_id->errors($e->getMessage());
                }
            });

            // name
            $input->name->cast('string')->length(1, 255);

            // name_normalized
            $input->name_normalized->cast('string')->length(1, 255);

            // name_soundex
            $input->name_soundex->cast('string')->length(1, 255);

            // iso2
            $input->iso2->cast('string')->length(1, 2)->callback(function($iso2) {
                $id_arr = region_dao::by_iso2($iso2->val());
                if (is_array($id_arr) && count($id_arr)) {
                    $iso2->errors('already in use');
                }
            });

            // longitude
            $input->longitude->cast('string');

            // latitude
            $input->latitude->cast('string');
        }

        public static function _validate_update(region_model $region, input_collection $input) {

            // country_id
            $input->country_id->cast('int')->optional()->digit(0, 255)->callback(function($country_id) use ($region) {
                $id_arr = region_dao::by_country($country_id->val());
                if (is_array($id_arr) && count($id_arr) && (int) current($id_arr) !== $region->id) {
                    $country_id->errors('already in use');
                }
            })->callback(function($country_id) use ($region) {
                $id_arr = region_dao::by_country($country_id->val());
                if (is_array($id_arr) && count($id_arr) && (int) current($id_arr) !== $region->id) {
                    $country_id->errors('already in use');
                }
            })->callback(function($country_id){
                try {
                    $country_id->data('model', new country_model($country_id->val()));
                } catch (country_exception $e) {
                    $country_id->errors($e->getMessage());
                }
            });

            // name
            $input->name->cast('string')->optional()->length(1, 255);

            // name_normalized
            $input->name_normalized->cast('string')->optional()->length(1, 255);

            // name_soundex
            $input->name_soundex->cast('string')->optional()->length(1, 255);

            // iso2
            $input->iso2->cast('string')->optional()->length(1, 2)->callback(function($iso2) use ($region) {
                $id_arr = region_dao::by_iso2($iso2->val());
                if (is_array($id_arr) && count($id_arr) && (int) current($id_arr) !== $region->id) {
                    $iso2->errors('already in use');
                }
            });

            // longitude
            $input->longitude->cast('string')->optional();

            // latitude
            $input->latitude->cast('string')->optional();
        }

    }
