<?php

    namespace Neoform\Locale\Nspace;

    use Neoform\Input;
    use Neoform\Entity;

    class Api {

        public static function insert(array $info) {

            $input = new Input\Collection($info);

            self::_validate_insert($input);

            if ($input->isValid()) {
                return Dao::get()->insert(
                    $input->getVals([
                        'name',
                    ])
                );
            }
            throw $input->getException();
        }

        public static function update(Model $locale_namespace, array $info, $includeEmpty=false) {

            $input = new Input\Collection($info);

            self::_validate_update($locale_namespace, $input, $includeEmpty);

            if ($input->isValid()) {
                return Dao::get()->update(
                    $locale_namespace,
                    $input->getVals(
                        [
                            'name',
                        ],
                        $includeEmpty
                    )
                );
            }
            throw $input->getException();
        }

        public static function delete(Model $locale_namespace) {
            return Dao::get()->delete($locale_namespace);
        }

        public static function _validate_insert(Input\Collection $input) {

            // name
            $input->validate('name', 'string')
                ->requireLength(1, 255)
                ->callback(function(Input\Input $name) {
                    if (Dao::get()->by_name($name->getVal())) {
                        $name->setErrors('already in use');
                    }
                });
        }

        public static function _validate_update(Model $locale_namespace, Input\Collection $input, $includeEmpty) {

            // name
            $input->validate('name', 'string', !$includeEmpty)
                ->requireLength(1, 255)
                ->callback(function(Input\Input $name) use ($locale_namespace) {
                    $id_arr = Dao::get()->by_name($name->getVal());
                    if (is_array($id_arr) && $id_arr && (int) current($id_arr) !== $locale_namespace->id) {
                        $name->setErrors('already in use');
                    }
                });
        }
    }
