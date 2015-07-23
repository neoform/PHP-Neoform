<?php

    namespace Neoform\Locale\Nspace;

    use Neoform\Input;
    use Neoform\Entity;

    class Api {

        public static function insert(array $info) {

            $input = new Input\Collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {
                return Entity::dao('Neoform\Locale\Nspace')->insert([
                    'name' => $input->name->val(),
                ]);
            }
            throw $input->exception();
        }

        public static function update(Model $locale_namespace, array $info, $crush=false) {

            $input = new Input\Collection($info);

            self::_validate_update($locale_namespace, $input);

            if ($input->is_valid()) {
                return Entity::dao('Neoform\Locale\Nspace')->update(
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

        public static function delete(Model $locale_namespace) {
            return Entity::dao('Neoform\Locale\Nspace')->delete($locale_namespace);
        }

        public static function _validate_insert(Input\Collection $input) {

            // name
            $input->name->cast('string')->length(1, 255)->callback(function($name) {
                if (Entity::dao('Neoform\Locale\Nspace')->by_name($name->val())) {
                    $name->errors('already in use');
                }
            });
        }

        public static function _validate_update(Model $locale_namespace, Input\Collection $input) {

            // name
            $input->name->cast('string')->optional()->length(1, 255)->callback(function($name) use ($locale_namespace) {
                $id_arr = Entity::dao('Neoform\Locale\Nspace')->by_name($name->val());
                if (is_array($id_arr) && $id_arr && (int) current($id_arr) !== $locale_namespace->id) {
                    $name->errors('already in use');
                }
            });
        }
    }
