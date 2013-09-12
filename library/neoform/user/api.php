<?php

    namespace neoform\user;

    use neoform\input;
    use neoform\entity;

    class api {

        public static function insert(array $info) {

            $input = new input\collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {
                $hashmethod      = lib::default_hashmethod();
                $hashmethod_cost = lib::default_hashmethod_cost();
                $salt            = lib::generate_salt();
                $hash            = $hashmethod->hash($input->password1->val(), $salt, $hashmethod_cost);

                $user = entity::dao('neoform\user')->insert([
                    'email'               => $input->email->val(),
                    'password_hash'       => $hash,
                    'password_hashmethod' => $hashmethod->id,
                    'password_cost'       => $hashmethod_cost,
                    'password_salt'       => $salt,
                    'status_id'           => lib::default_status()->id,
                ]);

                entity::dao('neoform\user\date')->insert([
                    'user_id' => $user->id,
                ]);

                return $user;
            }
            throw $input->exception();
        }

        public static function update_email(model $user, array $info) {

            $input = new input\collection($info);

            self::_validate_update_email($user, $input);

            if ($input->is_valid()) {
                return entity::dao('neoform\user')->update(
                    $user,
                    [
                        'email' => $input->email->val(),
                    ]
                );
            }
            throw $input->exception();
        }

        public static function update_password(model $user, array $info) {

            $input = new input\collection($info);

            self::_validate_update_password($user, $input);

            if ($input->is_valid()) {

                $salt          = lib::generate_salt();
                $password_cost = lib::default_hashmethod_cost();
                $hash_method   = lib::default_hashmethod();

                return entity::dao('neoform\user')->update(
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

        public static function admin_update(model $user, array $info) {

            $input = new input\collection($info);

            self::_validate_admin_update($user, $input);

            if ($input->is_valid()) {
                return entity::dao('neoform\user')->update(
                    $user,
                    [
                        'email'     => $input->email->val(),
                        'status_id' => $input->status_id->val(),
                    ]
                );
            }
            throw $input->exception();
        }

        public static function admin_password_update(model $user, array $info) {

            $input = new input\collection($info);

            self::_validate_admin_password_update($user, $input);

            if ($input->is_valid()) {

                return entity::dao('neoform\user')->update(
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

            $input = new input\collection($info);

            $input->email->cast('string')->trim()->tolower()->is_email();
            if ($input->is_valid()) {
                return ! (bool) current(entity::dao('neoform\user')->by_email($input->email->val()));
            } else {
                throw $input->exception();
            }
        }

        public static function _validate_insert(input\collection $input) {

            // email
            $input->email->cast('string')->length(1, 255)->is_email()->callback(function($email) {
                if (entity::dao('neoform\user')->by_email($email->val())) {
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

        public static function _validate_update_email(model $user, input\collection $input) {

            // email
            $input->email->cast('string')->length(1, 255)->is_email()->callback(function($email) use ($user) {
                $id_arr = entity::dao('neoform\user')->by_email($email->val());
                if (is_array($id_arr) && $id_arr && (int) current($id_arr) !== $user->id) {
                    $email->errors('already in use');
                }
            });
        }

        public static function _validate_update_password(model $user, input\collection $input) {

            // current password
            $input->current_password->cast('string')->callback(function($password) use ($user) {
                if (! lib::password_matches($user, $password->val())) {
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

        public static function _validate_admin_insert(input\collection $input) {

            // email
            $input->email->cast('string')->length(1, 255)->callback(function($email) {
                if (entity::dao('neoform\user')->by_email($email->val())) {
                    $email->errors('already in use');
                }
            });

            // password_hash
            $input->password->cast('string')->length(1, 255);

            // password_hashmethod
            $input->password_hashmethod->cast('int')->digit(0, 255)->callback(function($password_hashmethod){
                try {
                    $password_hashmethod->data('model', new hashmethod\model($password_hashmethod->val()));
                } catch (hashmethod\exception $e) {
                    $password_hashmethod->errors($e->getMessage());
                }
            });

            // password_cost
            $input->password_cost->cast('int')->digit(0, 4294967295);

            // password_salt
            $input->password_salt->cast('string')->length(1, 40);

            // status
            $input->status_id->cast('int')->digit(0, 255)->callback(function($status_id){
                try {
                    $status_id->data('model', new status\model($status_id->val()));
                } catch (status\exception $e) {
                    $status_id->errors($e->getMessage());
                }
            });
        }

        public static function _validate_admin_update(model $user, input\collection $input) {

            // email
            $input->email->cast('string')->length(1, 255)->callback(function($email) use ($user) {
                $id_arr = entity::dao('neoform\user')->by_email($email->val());
                if (is_array($id_arr) && $id_arr && (int) current($id_arr) !== $user->id) {
                    $email->errors('already in use');
                }
            });

            // status
            $input->status_id->cast('int')->digit(0, 255)->callback(function($status_id){
                try {
                    $status_id->data('model', new status\model($status_id->val()));
                } catch (status\exception $e) {
                    $status_id->errors($e->getMessage());
                }
            });
        }

        public static function _validate_admin_password_update(model $user, input\collection $input) {

            // password_hash
            $input->password->cast('string')->length(6, 255);

            // password_hashmethod
            $input->password_hashmethod->cast('int')->digit(0, 255)->callback(function($password_hashmethod){
                try {
                    $password_hashmethod->data('model', new hashmethod\model($password_hashmethod->val()));
                } catch (hashmethod\exception $e) {
                    $password_hashmethod->errors($e->getMessage());
                }
            });

            // password_cost
            $input->password_cost->cast('int')->digit(0, 4294967295);

            // password_salt
            $input->password_salt->cast('string')->length(1, 40);
        }
    }
