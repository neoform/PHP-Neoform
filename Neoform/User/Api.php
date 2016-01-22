<?php

    namespace Neoform\User;

    use Neoform\Input;
    use Neoform\Entity;

    class Api {

        public static function insert(array $info) {

            $input = new Input\Collection($info);

            self::_validate_insert($input);

            if ($input->isValid()) {
                $hashmethod      = Lib::default_hashmethod();
                $hashmethod_cost = Lib::default_hashmethod_cost();
                $salt            = Lib::generate_salt();
                $hash            = $hashmethod->hash($input->password1->getVal(), $salt, $hashmethod_cost);

                $user = Dao::get()->insert([
                    'email'               => $input->email->getVal(),
                    'password_hash'       => $hash,
                    'password_hashmethod' => $hashmethod->id,
                    'password_cost'       => $hashmethod_cost,
                    'password_salt'       => $salt,
                    'status_id'           => Lib::default_status()->id,
                ]);

                Date\Dao::get()->insert([
                    'user_id' => $user->id,
                ]);

                return $user;
            }
            throw $input->getException();
        }

        public static function update_email(Model $user, array $info) {

            $input = new Input\Collection($info);

            self::_validate_update_email($user, $input);

            if ($input->isValid()) {
                return Dao::get()->update(
                    $user,
                    [
                        'email' => $input->email->getVal(),
                    ]
                );
            }
            throw $input->getException();
        }

        public static function update_password(Model $user, array $info) {

            $input = new Input\Collection($info);

            self::_validate_update_password($user, $input);

            if ($input->isValid()) {

                $salt          = Lib::generate_salt();
                $password_cost = Lib::default_hashmethod_cost();
                $hash_method   = Lib::default_hashmethod();

                return Dao::get()->update(
                    $user,
                    [
                        'password_salt'       => $salt,
                        'password_cost'       => $password_cost,
                        'password_hashmethod' => $hash_method->id,
                        'password_hash'       => $hash_method->hash(
                            $input->password1->getVal(),
                            $salt,
                            $password_cost
                        ),
                    ]
                );
            }
            throw $input->getException();
        }

        public static function admin_insert(array $info) {

            $input = new Input\Collection($info);

            self::_validate_admin_insert($input);

            if ($input->isValid()) {

                $hashmethod = $input->password_hashmethod->getData('model');

                $user = Dao::get()->insert([
                    'email'               => $input->email->getVal(),
                    'password_hash'       => $hashmethod->hash(
                        $input->password->getVal(),
                        $input->password_salt->getVal(),
                        $input->password_cost->getVal()
                    ),
                    'password_hashmethod' => $hashmethod->id,
                    'password_cost'       => $input->password_cost->getVal(),
                    'password_salt'       => $input->password_salt->getVal(),
                    'status_id'           => $input->status_id->getData('model')->id,
                ]);

                Date\Dao::get()->insert([
                    'user_id' => $user->id,
                ]);

                return $user;
            }
            throw $input->getException();
        }

        public static function admin_update(Model $user, array $info) {

            $input = new Input\Collection($info);

            self::_validate_admin_update($user, $input);

            if ($input->isValid()) {
                return Dao::get()->update(
                    $user,
                    [
                        'email'     => $input->email->getVal(),
                        'status_id' => $input->status_id->getVal(),
                    ]
                );
            }
            throw $input->getException();
        }

        public static function admin_password_update(Model $user, array $info) {

            $input = new Input\Collection($info);

            self::_validate_admin_password_update($user, $input);

            if ($input->isValid()) {
                return Dao::get()->update(
                    $user,
                    [
                        'password_salt'       => $input->password_salt->getVal(),
                        'password_cost'       => $input->password_cost->getVal(),
                        'password_hashmethod' => $input->password_hashmethod->getData('model')->id,
                        'password_hash'       => $input->password_hashmethod->getData('model')->hash(
                            $input->password->getVal(),
                            $input->password_salt->getVal(),
                            $input->password_cost->getVal()
                        ),
                    ]
                );
            }
            throw $input->getException();
        }

        public static function email_available(array $info) {

            $input = new Input\Collection($info);

            $input->validate('email', 'string')
                ->trim()
                ->toLower()
                ->isEmail();

            if ($input->isValid()) {
                return ! (bool) current(Dao::get()->by_email($input->email->getVal()));
            } else {
                throw $input->getException();
            }
        }

        public static function _validate_insert(Input\Collection $input) {

            // email
            $input->validate('email', 'string')
                ->requireLength(1, 255)
                ->isEmail()
                ->toLower()
                ->callback(function(Input\Input $email) {
                    if (Dao::get()->by_email($email->getVal())) {
                        $email->setErrors('already in use');
                    }
                });

            // password1
            $input->validate('password1', 'string')
                ->requireLength(6, 1000);

            // password2
            $input->validate('password2', 'string')
                ->requireLength(6, 1000);

            if ($input->password1->getVal() !== $input->password2->getVal()) {
                $input->password2->setErrors('password does not match');
            }
        }

        public static function _validate_update_email(Model $user, Input\Collection $input) {

            // email
            $input->validate('email', 'string')
                ->requireLength(1, 255)
                ->isEmail()
                ->toLower()
                ->callback(function(Input\Input $email) use ($user) {
                    $id_arr = Dao::get()->by_email($email->getVal());
                    if (is_array($id_arr) && $id_arr && (int) current($id_arr) !== $user->id) {
                        $email->setErrors('already in use');
                    }
                });
        }

        public static function _validate_update_password(Model $user, Input\Collection $input) {

            // current password
            $input->validate('current_password', 'string')
                ->callback(function(Input\Input $password) use ($user) {
                    if (! Lib::password_matches($user, $password->getVal())) {
                        $password->setErrors('does not match');
                    }
                });

            // password1
            $input->validate('password1', 'string')
                ->requireLength(6, 1000);

            // password2
            $input->validate('password2', 'string')
                ->requireLength(6, 1000);

            if (! $input->password1->getErrors() && ! $input->password2->getErrors() && $input->password1->getVal() !== $input->password2->getVal()) {
                $input->password2->setErrors('password does not match');
            }
        }

        public static function _validate_admin_insert(Input\Collection $input) {

            // email
            $input->validate('email', 'string')
                ->requireLength(1, 255)
                ->isEmail()
                ->toLower()
                ->callback(function(Input\Input $email) {
                    if (Dao::get()->by_email($email->getVal())) {
                        $email->setErrors('already in use');
                    }
                });

            // password_hash
            $input->validate('password', 'string')
                ->requireLength(6, 255);

            // password_hashmethod
            $input->validate('password_hashmethod', 'int')
                ->requireDigit(0, 255)
                ->callback(function(Input\Input $password_hashmethod){
                    try {
                        $password_hashmethod->setData('model', Hashmethod\Model::fromPk($password_hashmethod->getVal()));
                    } catch (Hashmethod\Exception $e) {
                        $password_hashmethod->setErrors($e->getMessage());
                    }
                });

            // password_cost
            $input->validate('password_cost', 'int')
                ->requireDigit(1, 4294967295);

            // password_salt
            $input->validate('password_salt', 'string')
                ->requireLength(1, 40);

            // status
            $input->validate('status_id', 'int')
                ->requireDigit(0, 255)
                ->callback(function(Input\Input $status_id){
                    try {
                        $status_id->setData('model', Status\Model::fromPk($status_id->getVal()));
                    } catch (Status\Exception $e) {
                        $status_id->setErrors($e->getMessage());
                    }
                });
        }

        public static function _validate_admin_update(Model $user, Input\Collection $input) {

            // email
            $input->validate('email', 'string')
                ->requireLength(1, 255)
                ->isEmail()
                ->toLower()
                ->callback(function(Input\Input $email) use ($user) {
                    $id_arr = Dao::get()->by_email($email->getVal());
                    if (is_array($id_arr) && $id_arr && (int) current($id_arr) !== $user->id) {
                        $email->setErrors('already in use');
                    }
                });

            // status
            $input->validate('status_id', 'int')
                ->requireDigit(0, 255)
                ->callback(function(Input\Input $status_id){
                    try {
                        $status_id->setData('model', Status\Model::fromPk($status_id->getVal()));
                    } catch (status\Exception $e) {
                        $status_id->setErrors($e->getMessage());
                    }
                });
        }

        public static function _validate_admin_password_update(Model $user, Input\Collection $input) {

            // password_hash
            $input->validate('password', 'string')
                ->requireLength(6, 255);

            // password_hashmethod
            $input->validate('password_hashmethod', 'int')
                ->requireDigit(0, 255)
                ->callback(function(Input\Input $password_hashmethod){
                    try {
                        $password_hashmethod->setData('model', Hashmethod\Model::fromPk($password_hashmethod->getVal()));
                    } catch (hashmethod\Exception $e) {
                        $password_hashmethod->setErrors($e->getMessage());
                    }
                });

            // password_cost
            $input->validate('password_cost', 'int')
                ->requireDigit(1, 4294967295);

            // password_salt
            $input->validate('password_salt', 'string')
                ->requireLength(1, 40);
        }
    }
