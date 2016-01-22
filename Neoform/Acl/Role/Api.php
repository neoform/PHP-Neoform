<?php

    namespace Neoform\Acl\Role;

    use Neoform\Input;
    use Neoform\Entity;

    class Api {

        /**
         * Creates a Acl Role model with $info
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
         * Update a Acl Role model with $info
         *
         * @param Model $acl_role
         * @param array $info
         * @param bool  $includeEmpty
         *
         * @return Model
         * @throws Input\Exception
         */
        public static function update(Model $acl_role, array $info, $includeEmpty=false) {

            $input = new Input\Collection($info);

            self::_validate_update($acl_role, $input, $includeEmpty);

            if ($input->isValid()) {
                return Dao::get()->update(
                    $acl_role,
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
         * Delete a Acl Role
         *
         * @param Model $acl_role
         *
         * @return bool
         */
        public static function delete(Model $acl_role) {
            return Dao::get()->delete($acl_role);
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
         * Validates info to update a Acl Role model
         *
         * @param Model $acl_role
         * @param Input\Collection $input
         * @param bool $includeEmpty
         */
        public static function _validate_update(Model $acl_role, Input\Collection $input, $includeEmpty) {

            // name
            $input->validate('name', 'string', !$includeEmpty)
                ->requireLength(1, 64)
                ->callback(function(Input\Input $name) use ($acl_role) {
                    $id_arr = Dao::get()->by_name($name->getVal());
                    if (is_array($id_arr) && $id_arr && (int) current($id_arr) !== $acl_role->id) {
                        $name->setErrors('already in use');
                    }
                });
        }
    }
