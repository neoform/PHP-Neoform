<?php

    namespace Neoform\User\Lostpassword;

    use Neoform\Input;
    use Neoform\Entity;
    use Neoform\Auth;
    use Neoform\User;
    use Neoform\Redirect;
    use Neoform;

    class Api {

        /**
         * @param Neoform\Request\Model $request
         * @param Neoform\Site\Model $site
         * @param array $info
         *
         * @return bool
         * @throws Input\exception
         * @throws Neoform\Error\Exception
         * @throws Redirect\Exception
         */
        public static function lost(Neoform\Request\Model $request, Neoform\Site\Model $site, array $info) {

            if ($request->getSession()->getAuth()->isLoggedIn()) {
                throw new Redirect\Exception;
            }

            $input = new Input\Collection($info);

            $input->validate('email', 'string')
                ->trim()
                ->requireLength(1,255)
                ->isEmail()
                ->callback(function(Input\Input $email) use ($site) {
                    if (! $email->getErrors()) {
                        if ($user_id = current(User\Dao::get()->by_email($email->getVal()))) {
                            $user = User\Model::fromPk($user_id);
                            if (count(Neoform\User\Site\Dao::get()->by_site_user($site->id, $user->id))) {
                                return $email->setData('model', $user);
                            }
                        }
                        $email->setErrors('Email address not found');
                    }
                });

            if ($input->isValid()) {

                $user = $input->email->getData('model');

                //delete all previous reset keys
                Dao::get()->deleteMulti(
                    User\Lostpassword\Collection::fromPks(
                        Dao::get()->by_user($user->id)
                    )
                );

                // Generate random hash
                $hash = Neoform\Type\String\Lib::random_chars(40);

                Dao::get()->insert([
                    'user_id' => $user->id,
                    'hash'    => $hash,
                ]);

                //email the request to the friend to tell them the good news
                $email            = new Neoform\Email\Model('password/lost');
                $email->url       = "{$request->getBaseUrl()->getSecureBaseUrl()}/account/passwordreset/{$hash}";
                $email->site_name = Neoform\Core\Config::get()->getSiteName();

                try {
                    $email->send($user->email, 'html');
                    return true;
                } catch (Neoform\Email\Exception $e) {
                    throw new Neoform\Error\Exception($e->getMessage());
                }
            }

            throw $input->getException();
        }

        /**
         * @param Neoform\Request\Model $request
         * @param Neoform\Site\Model $site
         * @param $hash
         *
         * @return array
         * @throws Neoform\Error\Exception
         * @throws Redirect\Exception
         * @throws User\Exception
         */
        public static function find(Neoform\Request\Model $request, Neoform\Site\Model $site, $hash) {
            if ($request->getSession()->getAuth()->isLoggedIn()) {
                throw new Neoform\Redirect\Exception;
            }

            try {
                $lost_password = User\Lostpassword\Model::fromPk($hash);
            } catch (User\Lostpassword\Exception $e) {
                throw new Neoform\Error\Exception('You password reset link has either expired or is not valid');
            }

            $password      = Neoform\Type\String\Lib::random_chars(8, 12);
            $salt          = User\Lib::generate_salt();
            $password_cost = User\Lib::default_hashmethod_cost();
            $hash_method   = User\Lib::default_hashmethod();
            $user          = $lost_password->user();

            User\Dao::get()->update(
                $user,
                [
                    'password_salt'       => $salt,
                    'password_cost'       => $password_cost,
                    'password_hashmethod' => $hash_method->id,
                    'password_hash'       => $hash_method->hash($password, $salt, $password_cost),
                ]
            );

            Dao::get()->delete($lost_password);

            return [$user, $password];
        }

        public static function insert(array $info) {

            $input = new Input\Collection($info);

            self::_validate_insert($input);

            if ($input->isValid()) {
                return Dao::get()->insert(
                    $input->getVals([
                        'hash',
                        'user_id',
                        'posted_on',
                    ])
                );
            }
            throw $input->getException();
        }

        public static function update(Model $user_lostpassword, array $info, $includeEmpty=false) {

            $input = new Input\Collection($info);

            self::_validate_update($user_lostpassword, $input, $includeEmpty);

            if ($input->isValid()) {
                return Dao::get()->update(
                    $user_lostpassword,
                    $input->getVals(
                        [
                            'hash',
                            'user_id',
                            'posted_on',
                        ],
                        $includeEmpty
                    )
                );
            }
            throw $input->getException();
        }

        public static function delete(Model $user_lostpassword) {
            return Dao::get()->delete($user_lostpassword);
        }

        public static function _validate_insert(Input\Collection $input) {

            // hash
            $input->validate('hash', 'string')
                ->requireLength(1, 40)
                ->callback(function(Input\Input $hash) {
                    if (Dao::get()->record($hash->getVal())) {
                        $hash->setErrors('already in use');
                    }
                });

            // user_id
            $input->validate('user_id', 'int')
                ->requireDigit(0, 4294967295)
                ->callback(function(Input\Input $user_id) {
                    if (Dao::get()->by_user($user_id->getVal())) {
                        $user_id->setErrors('already in use');
                    }
                })->callback(function(Input\Input $user_id) {
                    try {
                        $user_id->setData('model', User\Model::fromPk($user_id->getVal()));
                    } catch (User\Exception $e) {
                        $user_id->setErrors($e->getMessage());
                    }
                });

            // posted_on
            $input->validate('posted_on', 'string', true)
                ->isDateTime();
        }

        public static function _validate_update(Model $user_lostpassword, Input\Collection $input, $includeEmpty) {

            // hash
            $input->validate('hash', 'string', !$includeEmpty)
                ->requireLength(1, 40)
                ->callback(function(Input\Input $hash) use ($user_lostpassword) {
                    $user_lostpassword_info = Dao::get()->record($hash->getVal());
                    if ($user_lostpassword_info && (string) $user_lostpassword_info['hash'] !== $user_lostpassword->hash) {
                        $hash->setErrors('already in use');
                    }
                });

            // user_id
            $input->validate('user_id', 'int', !$includeEmpty)
                ->requireDigit(0, 4294967295)
                ->callback(function(Input\Input $user_id) use ($user_lostpassword) {
                    $hash_arr = Dao::get()->by_user($user_id->getVal());
                    if (is_array($hash_arr) && $hash_arr && (string) current($hash_arr) !== $user_lostpassword->hash) {
                        $user_id->setErrors('already in use');
                    }
                })
                ->callback(function(Input\Input $user_id) {
                    try {
                        $user_id->setData('model', User\Model::fromPk($user_id->getVal()));
                    } catch (User\Exception $e) {
                        $user_id->setErrors($e->getMessage());
                    }
                });

            // posted_on
            $input->validate('posted_on', 'string', !$includeEmpty)
                ->isDateTime();
        }
    }
