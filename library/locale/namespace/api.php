<?php

    class locale_namespace_api {

        public static function insert(array $info) {

            $input = new input_collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {
                return locale_namespace_dao::insert([
                    'name' => $input->name->val(),
                ]);
            }
            throw $input->exception();
        }

        public static function update(locale_namespace_model $locale_namespace, array $info, $crush=false) {

            $input = new input_collection($info);

            self::_validate_update($locale_namespace, $input);

            if ($input->is_valid()) {
                return locale_namespace_dao::update(
                    $locale_namespace,
                    $input->vals(
                        [
                            'name',
                        ],
                        $crush
                    )
                );
            }
            throw $input->exception();
        }

        public static function delete(locale_namespace_model $locale_namespace) {
            return locale_namespace_dao::delete($locale_namespace);
        }

        public static function _validate_insert(input_collection $input) {

            // name
            $input->name->cast('string')->length(1, 255)->callback(function($name) {
                $id_arr = locale_namespace_dao::by_name($name->val());
                if (is_array($id_arr) && count($id_arr)) {
                    $name->errors('already in use');
                }
            });
        }

        public static function _validate_update(locale_namespace_model $locale_namespace, input_collection $input) {

            // name
            $input->name->cast('string')->optional()->length(1, 255)->callback(function($name) use ($locale_namespace) {
                $id_arr = locale_namespace_dao::by_name($name->val());
                if (is_array($id_arr) && count($id_arr) && (int) current($id_arr) !== $locale_namespace->id) {
                    $name->errors('already in use');
                }
            });
        }
    }
