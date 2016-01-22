<?php

    namespace Neoform\Acl\Group;

    use Neoform\Input;
    use Neoform\Entity;

    class Api {

        /**
         * Creates a Acl Group model with $info
         *
         * @param array $info
         *
         * @return Model
         * @throws Input\Exception
         */
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

        /**
         * Update a Acl Group model with $info
         *
         * @param Model $acl_group
         * @param array $info
         * @param bool  $includeEmpty
         *
         * @return Model
         * @throws Input\Exception
         */
        public static function update(Model $acl_group, array $info, $includeEmpty=false) {

            $input = new Input\Collection($info);

            self::_validate_update($acl_group, $input, $includeEmpty);

            if ($input->isValid()) {
                return Dao::get()->update(
                    $acl_group,
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

        /**
         * Delete a Acl Group
         *
         * @param Model $acl_group
         *
         * @return bool
         */
        public static function delete(Model $acl_group) {
            return Dao::get()->delete($acl_group);
        }

        /**
         * Validates info to for insert
         *
         * @param Input\Collection $input
         */
        public static function _validate_insert(Input\Collection $input) {

            // name
            $input->validate('name', 'string')
                ->requireLength(1, 64)
                ->callback(function(Input\Input $name) {
                    if (Dao::get()->by_name($name->getVal())) {
                        $name->setErrors('already in use');
                    }
                });
        }

        /**
         * Validates info to update a Acl Group model
         *
         * @param Model $acl_group
         * @param Input\Collection $input
         */
        public static function _validate_update(Model $acl_group, Input\Collection $input, $includeEmpty) {

            // name
            $input->validate('name', 'string', !$includeEmpty)
                ->requireLength(1, 64)
                ->callback(function(Input\Input $name) use ($acl_group) {
                    $id_arr = Dao::get()->by_name($name->getVal());
                    if (is_array($id_arr) && $id_arr && (int) current($id_arr) !== $acl_group->id) {
                        $name->setErrors('already in use');
                    }
                });
        }
    }
