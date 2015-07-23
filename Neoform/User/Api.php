<?php

    namespace Neoform\User;

    use Neoform\Input;
    use Neoform\Entity;

    class Api {

        public static function insert(array $info) {

            $input = new Input\Collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {
                $hashmethod      = Lib::default_hashmethod();
                $hashmethod_cost = Lib::default_hashmethod_cost();
                $salt            = Lib::generate_salt();
                $hash            = $hashmethod->hash($input->password1->val(), $salt, $hashmethod_cost);

                $user = Entity::dao('Neoform\User')->insert([
                    'email'               => $input->email->val(),
                    'password_hash'       => $hash,
                    'password_hashmethod' => $hashmethod->id,
                    'password_cost'       => $hashmethod_cost,
                    'password_salt'       => $salt,
                    'status_id'           => Lib::default_status()->id,
                ]);

                Entity::dao('Neoform\User\Date')->insert([
                    'user_id' => $user->id,
                ]);

                return $user;
            }
            throw $input->exception();
        }

        public static function update_email(Model $user, array $info) {

            $input = new Input\Collection($info);

            self::_validate_update_email($user, $input);

            if ($input->is_valid()) {
                return Entity::dao('Neoform\User')->update(
                    $user,
                    [
                        'email' => $input->email->val(),
                    ]
                );
            }
            throw $input->exception();
        }

        public static function update_password(Model $user, array $info) {

            $input = new Input\Collection($info);

            self::_validate_update_password($user, $input);

            if ($input->is_valid()) {

                $salt          = Lib::generate_salt();
                $password_cost = Lib::default_hashmethod_cost();
                $hash_method   = Lib::default_hashmethod();

                return Entity::dao('Neoform\User')->update(
                    $user,
                    [
                        'password_salt'       => $salt,
                        'password_cost'       => $password_cost,
                        'password_hashmethod' => $hash_method->id,
                        'password_hash'       => $hash_method->hash(
                            $input->password1->val(),
                            $salt,
                            $password_cost
                        ),
                    ]
                );
            }
            throw $input->exception();
        }

        public static function admin_insert(array $info) {

            $input = new Input\Collection($info);

            self::_validate_admin_insert($input);

            if ($input->is_valid()) {

                $hashmethod = $input->password_hashmethod->data('model');

                $user = Entity::dao('Neoform\User')->insert([
                    'email'               => $input->email->val(),
                    'password_hash'       => $hashmethod->hash(
                        $input->password->val(),
                        $input->password_salt->val(),
                        $input->password_cost->val()
                    ),
                    'password_hashmethod' => $hashmethod->id,
                    'password_cost'       => $input->password_cost->val(),
                    'password_salt'       => $input->password_salt->val(),
                    'status_id'           => $input->status_id->data('model')->id,
                ]);

                Entity::dao('Neoform\User\Date')->insert([
                    'user_id' => $user->id,
                ]);

                return $user;
            }
            throw $input->exception();
        }

        public static function admin_update(Model $user, array $info) {

            $input = new Input\Collection($info);

            self::_validate_admin_update($user, $input);

            if ($input->is_valid()) {
                return Entity::dao('Neoform\User')->update(
                    $user,
                    [
                        'email'     => $input->email->val(),
                        'status_id' => $input->status_id->val(),
                    ]
                );
            }
            throw $input->exception();
        }

        public static function admin_password_update(Model $user, array $info) {

            $input = new Input\Collection($info);

            self::_validate_admin_password_update($user, $input);

            if ($input->is_valid()) {

                return Entity::dao('Neoform\User')->update(
                    $user,
                    [
                        'password_salt'       => $input->password_salt->val(),
                        'password_cost'       => $input->password_cost->val(),
                        'password_hashmethod' => $input->password_hashmethod->data('model')->id,
                        'password_hash'       => $input->password_hashmethod->data('model')->hash(
                            $input->password->val(),
                            $input->password_salt->val(),
                            $input->password_cost->val()
                        ),
                    ]
                );
            }
            throw $input->exception();
        }

        public static function email_available(array $info) {

            $input = new Input\Collection($info);

            $input->email->cast('string')->trim()->tolower()->is_email();
            if ($input->is_valid()) {
                return ! (bool) current(Entity::dao('Neoform\User')->by_email($input->email->val()));
            } else {
                throw $input->exception();
            }
        }

        public static function _validate_insert(Input\Collection $input) {

            // email
            $input->email->cast('string')->length(1, 255)->is_email()->tolower()->callback(function($email) {
                if (Entity::dao('Neoform\User')->by_email($email->val())) {
                    $email->errors('already in use');
                }
            });

            // password1
            $input->password1->cast('string')->length(6, 1000);

            // password2
            $input->password2->cast('string')->length(6, 1000);

            if ($input->password1->val() !== $input->password2->val()) {
                $input->password2->errors('password does not match');
            }
        }

        public static function _validate_update_email(Model $user, Input\Collection $input) {

            // email
            $input->email->cast('string')->length(1, 255)->is_email()->tolower()->callback(function($email) use ($user) {
                $id_arr = Entity::dao('Neoform\User')->by_email($email->val());
                if (is_array($id_arr) && $id_arr && (int) current($id_arr) !== $user->id) {
                    $email->errors('already in use');
                }
            });
        }

        public static function _validate_update_password(Model $user, Input\Collection $input) {

            // current password
            $input->current_password->cast('string')->callback(function($password) use ($user) {
                if (! Lib::password_matches($user, $password->val())) {
                    $password->errors('does not match');
                }
            });

            // password1
            $input->password1->cast('string')->length(6, 1000);

            // password2
            $input->password2->cast('string')->length(6, 1000);

            if (! $input->password1->errors() && ! $input->password2->errors() && $input->password1->val() !== $input->password2->val()) {
                $input->password2->errors('password does not match');
            }
        }

        public static function _validate_admin_insert(Input\Collection $input) {

            // email
            $input->email->cast('string')->length(1, 255)->is_email()->tolower()->callback(function($email) {
                if (Entity::dao('Neoform\User')->by_email($email->val())) {
                    $email->errors('already in use');
                }
            });

            // password_hash
            $input->password->cast('string')->length(6, 255);

            // password_hashmethod
            $input->password_hashmethod->cast('int')->digit(0, 255)->callback(function($password_hashmethod){
                try {
                    $password_hashmethod->data('model', new Hashmethod\Model($password_hashmethod->val()));
                } catch (Hashmethod\Exception $e) {
                    $password_hashmethod->errors($e->getMessage());
                }
            });

            // password_cost
            $input->password_cost->cast('int')->digit(1, 4294967295);

            // password_salt
            $input->password_salt->cast('string')->length(1, 40);

            // status
            $input->status_id->cast('int')->digit(0, 255)->callback(function($status_id){
                try {
                    $status_id->data('model', new Status\Model($status_id->val()));
                } catch (Status\Exception $e) {
                    $status_id->errors($e->getMessage());
                }
            });
        }

        public static function _validate_admin_update(Model $user, Input\Collection $input) {

            // email
            $input->email->cast('string')->length(1, 255)->is_email()->tolower()->callback(function($email) use ($user) {
                $id_arr = Entity::dao('Neoform\User')->by_email($email->val());
                if (is_array($id_arr) && $id_arr && (int) current($id_arr) !== $user->id) {
                    $email->errors('already in use');
                }
            });

            // status
            $input->status_id->cast('int')->digit(0, 255)->callback(function($status_id){
                try {
                    $status_id->data('model', new Status\Model($status_id->val()));
                } catch (status\Exception $e) {
                    $status_id->errors($e->getMessage());
                }
            });
        }

        public static function _validate_admin_password_update(Model $user, Input\Collection $input) {

            // password_hash
            $input->password->cast('string')->length(6, 255);

            // password_hashmethod
            $input->password_hashmethod->cast('int')->digit(0, 255)->callback(function($password_hashmethod){
                try {
                    $password_hashmethod->data('model', new Hashmethod\Model($password_hashmethod->val()));
                } catch (hashmethod\Exception $e) {
                    $password_hashmethod->errors($e->getMessage());
                }
            });

            // password_cost
            $input->password_cost->cast('int')->digit(1, 4294967295);

            // password_salt
            $input->password_salt->cast('string')->length(1, 40);
        }
    }
