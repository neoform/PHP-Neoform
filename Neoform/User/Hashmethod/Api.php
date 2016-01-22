<?php

    namespace Neoform\User\Hashmethod;

    use Neoform\Input;
    use Neoform\Entity;

    class Api {

        /**
         * Creates a User Hashmethod model with $info
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
                        'id',
                        'name',
                    ])
                );
            }
            throw $input->getException();
        }

        /**
         * Update a User Hashmethod model with $info
         *
         * @param Model $user_hashmethod
         * @param array $info
         * @param bool  $includeEmpty
         *
         * @return Model
         * @throws Input\Exception
         */
        public static function update(Model $user_hashmethod, array $info, $includeEmpty=false) {

            $input = new Input\Collection($info);

            self::_validate_update($user_hashmethod, $input, $includeEmpty);

            if ($input->isValid()) {
                return Dao::get()->update(
                    $user_hashmethod,
                    $input->getVals(
                        [
                            'id',
                            'name',
                        ],
                        $includeEmpty
                    )
                );
            }
            throw $input->getException();
        }

        /**
         * Delete a User Hashmethod
         *
         * @param Model $user_hashmethod
         *
         * @return bool
         */
        public static function delete(Model $user_hashmethod) {
            return Dao::get()->delete($user_hashmethod);
        }

        /**
         * Validates info to for insert
         *
         * @param Input\Collection $input
         */
        public static function _validate_insert(Input\Collection $input) {

            // id
            $input->validate('id', 'int')
                ->requireDigit(0, 255)
                ->callback(function(Input\Input $id) {
                    if (Dao::get()->record($id->getVal())) {
                        $id->setErrors('already in use');
                    }
            });

            // name
            $input->validate('name', 'string')
                ->requireLength(1, 255)
                ->callback(function(Input\Input $name) {
                    if (Dao::get()->by_name($name->getVal())) {
                        $name->setErrors('already in use');
                    }
                });
        }

        /**
         * Validates info to update a User Hashmethod model
         *
         * @param Model $user_hashmethod
         * @param Input\Collection $input
         * @param bool $includeEmpty
         */
        public static function _validate_update(Model $user_hashmethod, Input\Collection $input, $includeEmpty) {

            // id
            $input->validate('id', 'int', !$includeEmpty)
                ->requireDigit(0, 255)
                ->callback(function(Input\Input $id) use ($user_hashmethod) {
                    $user_hashmethod_info = Dao::get()->record($id->getVal());
                    if ($user_hashmethod_info && (int) $user_hashmethod_info['id'] !== $user_hashmethod->id) {
                        $id->setErrors('already in use');
                    }
                });

            // name
            $input->validate('name', 'string', !$includeEmpty)
                ->requireLength(1, 255)
                ->callback(function(Input\Input $name) use ($user_hashmethod) {
                    $id_arr = Dao::get()->by_name($name->getVal());
                    if (is_array($id_arr) && $id_arr && (int) current($id_arr) !== $user_hashmethod->id) {
                        $name->setErrors('already in use');
                    }
                });
        }
    }
