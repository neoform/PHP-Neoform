<?php

    namespace neoform\city;

    use neoform\input;
    use neoform\entity;

    class api {

        /**
         * Creates a City model with $info
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
                return entity::dao('city')->insert([
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

        /**
         * Update a City model with $info
         *
         * @param model $city
         * @param array $info
         * @param bool  $crush
         *
         * @return model
         * @throws input\exception
         */
        public static function update(model $city, array $info, $crush=false) {

            $input = new input\collection($info);

            self::_validate_update($city, $input);

            if ($input->is_valid()) {
                return entity::dao('city')->update(
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

        /**
         * Delete a City
         *
         * @param model $city
         *
         * @return bool
         */
        public static function delete(model $city) {
            return entity::dao('city')->delete($city);
        }

        /**
         * Validates info to for insert
         *
         * @param input\collection $input
         */
        public static function _validate_insert(input\collection $input) {

            // region_id
            $input->region_id->cast('int')->digit(0, 65535)->callback(function($region_id) {
                try {
                    $region_id->data('model', new \neoform\region\model($region_id->val()));
                } catch (\neoform\region\exception $e) {
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

        /**
         * Validates info to update a City model
         *
         * @param model $city
         * @param input\collection $input
         */
        public static function _validate_update(model $city, input\collection $input) {

            // region_id
            $input->region_id->cast('int')->optional()->digit(0, 65535)->callback(function($region_id) {
                try {
                    $region_id->data('model', new \neoform\region\model($region_id->val()));
                } catch (\neoform\region\exception $e) {
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
