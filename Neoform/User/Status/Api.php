<?php

    namespace Neoform\User\Status;

    use Neoform\Input;
    use Neoform\Entity;

    class Api {

        /**
         * Creates a User Status model with $info
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
         * Update a User Status model with $info
         *
         * @param Model $user_status
         * @param array $info
         * @param bool  $includeEmpty
         *
         * @return Model
         * @throws Input\Exception
         */
        public static function update(Model $user_status, array $info, $includeEmpty=false) {

            $input = new Input\Collection($info);

            self::_validate_update($user_status, $input, $includeEmpty);

            if ($input->isValid()) {
                return Dao::get()->update(
                    $user_status,
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
         * Delete a User Status
         *
         * @param Model $user_status
         *
         * @return bool
         */
        public static function delete(Model $user_status) {
            return Dao::get()->delete($user_status);
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
         * Validates info to update a User Status model
         *
         * @param Model $user_status
         * @param Input\Collection $input
         * @param bool $includeEmpty
         */
        public static function _validate_update(Model $user_status, Input\Collection $input, $includeEmpty) {

            // id
            $input->validate('id', 'int', !$includeEmpty)
                ->requireDigit(0, 255)
                ->callback(function(Input\Input $id) use ($user_status) {
                    $user_status_info = Dao::get()->record($id->getVal());
                    if ($user_status_info && (int) $user_status_info['id'] !== $user_status->id) {
                        $id->setErrors('already in use');
                    }
                });

            // name
            $input->validate('name', 'string', !$includeEmpty)
                ->requireLength(1, 255)
                ->callback(function(Input\Input $name) use ($user_status) {
                    $id_arr = Dao::get()->by_name($name->getVal());
                    if (is_array($id_arr) && $id_arr && (int) current($id_arr) !== $user_status->id) {
                        $name->setErrors('already in use');
                    }
                });
        }
    }
