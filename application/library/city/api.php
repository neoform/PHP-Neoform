<?php

    class city_api {

        public static function insert(array $info) {

            $input = new input_collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {
                return entity_dao::get('city')->insert([
                    'region_id'       => $input->region_id->val(),
                    'name'            => $input->name->val(),
                    'name_normalized' => $input->name_normalized->val(),
                    'name_soundex'    => $input->name_soundex->val(),
                    'top'             => $input->top->val(),
                    'longitude'       => $input->longitude->val(),
                    'latitude'        => $input->latitude->val(),
                ]);
            }
            throw $input->exception();
        }

        public static function update(city_model $city, array $info, $crush=false) {

            $input = new input_collection($info);

            self::_validate_update($city, $input);

            if ($input->is_valid()) {
                return entity_dao::get('city')->update(
                    $city,
                    $input->vals(
                        [
                            'region_id',
                            'name',
                            'name_normalized',
                            'name_soundex',
                            'top',
                            'longitude',
                            'latitude',
                        ],
                        $crush
                    )
                );
            }
            throw $input->exception();
        }

        public static function delete(city_model $city) {
            return entity_dao::get('city')->delete($city);
        }

        public static function _validate_insert(input_collection $input) {

            // region_id
            $input->region_id->cast('int')->digit(0, 65535)->callback(function($region_id){
                try {
                    $region_id->data('model', new region_model($region_id->val()));
                } catch (region_exception $e) {
                    $region_id->errors($e->getMessage());
                }
            });

            // name
            $input->name->cast('string')->length(1, 255);

            // name_normalized
            $input->name_normalized->cast('string')->length(1, 255);

            // name_soundex
            $input->name_soundex->cast('string')->length(1, 255);

            // top
            $input->top->cast('string')->in(['yes','no']);

            // longitude
            $input->longitude->cast('float');

            // latitude
            $input->latitude->cast('float');
        }

        public static function _validate_update(city_model $city, input_collection $input) {

            // region_id
            $input->region_id->cast('int')->optional()->digit(0, 65535)->callback(function($region_id){
                try {
                    $region_id->data('model', new region_model($region_id->val()));
                } catch (region_exception $e) {
                    $region_id->errors($e->getMessage());
                }
            });

            // name
            $input->name->cast('string')->optional()->length(1, 255);

            // name_normalized
            $input->name_normalized->cast('string')->optional()->length(1, 255);

            // name_soundex
            $input->name_soundex->cast('string')->optional()->length(1, 255);

            // top
            $input->top->cast('string')->optional()->in(['yes','no']);

            // longitude
            $input->longitude->cast('float')->optional();

            // latitude
            $input->latitude->cast('float')->optional();
        }
    }
