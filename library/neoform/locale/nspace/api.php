<?php

    namespace neoform\locale\nspace;

    use neoform\input;
    use neoform\entity;

    class api {

        public static function insert(array $info) {

            $input = new input\collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {
                return entity::dao('locale\nspace')->insert([
                    'name' => $input->name->val(),
                ]);
            }
            throw $input->exception();
        }

        public static function update(model $locale_namespace, array $info, $crush=false) {

            $input = new input\collection($info);

            self::_validate_update($locale_namespace, $input);

            if ($input->is_valid()) {
                return entity::dao('locale\nspace')->update(
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

        public static function delete(model $locale_namespace) {
            return entity::dao('locale\nspace')->delete($locale_namespace);
        }

        public static function _validate_insert(input\collection $input) {

            // name
            $input->name->cast('string')->length(1, 255)->callback(function($name) {
                if (entity::dao('locale\nspace')->by_name($name->val())) {
                    $name->errors('already in use');
                }
            });
        }

        public static function _validate_update(model $locale_namespace, input\collection $input) {

            // name
            $input->name->cast('string')->optional()->length(1, 255)->callback(function($name) use ($locale_namespace) {
                $id_arr = entity::dao('locale\nspace')->by_name($name->val());
                if (is_array($id_arr) && $id_arr && (int) current($id_arr) !== $locale_namespace->id) {
                    $name->errors('already in use');
                }
            });
        }
    }
