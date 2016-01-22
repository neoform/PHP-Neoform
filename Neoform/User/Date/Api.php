<?php

    namespace Neoform\User\Date;

    use Neoform\Input;
    use Neoform\Entity;

    class Api {

        /**
         * Creates a User Date model with $info
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
                        'user_id',
                        'created_on',
                        'last_login',
                        'email_verified_on',
                        'password_updated_on',
                    ])
                );
            }
            throw $input->getException();
        }

        /**
         * Update a User Date model with $info
         *
         * @param Model $user_date
         * @param array $info
         * @param bool  $includeEmpty
         *
         * @return Model
         * @throws Input\Exception
         */
        public static function update(Model $user_date, array $info, $includeEmpty=false) {

            $input = new Input\Collection($info);

            self::_validate_update($user_date, $input, $includeEmpty);

            if ($input->isValid()) {
                return Dao::get()->update(
                    $user_date,
                    $input->getVals(
                        [
                            'user_id',
                            'created_on',
                            'last_login',
                            'email_verified_on',
                            'password_updated_on',
                        ],
                        $includeEmpty
                    )
                );
            }
            throw $input->getException();
        }

        /**
         * Delete a User Date
         *
         * @param Model $user_date
         *
         * @return bool
         */
        public static function delete(Model $user_date) {
            return Dao::get()->delete($user_date);
        }

        /**
         * Validates info to for insert
         *
         * @param Input\Collection $input
         */
        public static function _validate_insert(Input\Collection $input) {

            // user_id
            $input->validate('user_id', 'int')
                ->requireDigit(0, 4294967295)
                ->callback(function(Input\Input $user_id) {
                    if (Dao::get()->record($user_id->getVal())) {
                        $user_id->setErrors('already in use');
                    }
                })
                ->callback(function(Input\Input $user_id) {
                    try {
                        $user_id->setData('model', \Neoform\User\Model::fromPk($user_id->getVal()));
                    } catch (\Neoform\User\Exception $e) {
                        $user_id->setErrors($e->getMessage());
                    }
                });

            // created_on
            $input->validate('created_on', 'string', true)
                ->isDateTime();

            // last_login
            $input->validate('last_login', 'string', true)
                ->isDateTime();

            // email_verified_on
            $input->validate('email_verified_on', 'string', true)
                ->isDateTime();

            // password_updated_on
            $input->validate('password_updated_on', 'string', true)
                ->isDateTime();
        }

        /**
         * Validates info to update a User Date model
         *
         * @param Model $user_date
         * @param Input\Collection $input
         * @param bool $includeEmpty
         */
        public static function _validate_update(Model $user_date, Input\Collection $input, $includeEmpty) {

            // user_id
            $input->validate('user_id', 'int', !$includeEmpty)
                ->requireDigit(0, 4294967295)
                ->callback(function(Input\Input $user_id) use ($user_date) {
                    $user_date_info = Dao::get()->record($user_id->getVal());
                    if ($user_date_info && (int) $user_date_info['user_id'] !== $user_date->user_id) {
                        $user_id->setErrors('already in use');
                    }
                })
                ->callback(function(Input\Input $user_id) {
                    try {
                        $user_id->setData('model', \Neoform\User\Model::fromPk($user_id->getVal()));
                    } catch (\Neoform\User\Exception $e) {
                        $user_id->setErrors($e->getMessage());
                    }
                });

            // created_on
            $input->validate('created_on', 'string', true)
                ->isDateTime();

            // last_login
            $input->validate('last_login', 'string', true)
                ->isDateTime();

            // email_verified_on
            $input->validate('email_verified_on', 'string', true)
                ->isDateTime();

            // password_updated_on
            $input->validate('password_updated_on', 'string', true)
                ->isDateTime();
        }
    }
