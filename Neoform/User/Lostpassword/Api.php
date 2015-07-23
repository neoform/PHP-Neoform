<?php

    namespace Neoform\User\Lostpassword;

    use Neoform\Input;
    use Neoform\Entity;
    use Neoform\Auth;
    use Neoform\User;
    use Neoform\Redirect;
    use Neoform\Http;
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

            $input->email->cast('string')->trim()->length(1,255)->is_email()->callback(function($email) use ($site) {
                if (! $email->errors()) {
                    if ($user_id = current(Entity::dao('Neoform\User')->by_email($email->val()))) {
                        $user = new User\Model($user_id);
                        if (count(Entity::dao('Neoform\User\Site')->by_site_user($site->id, $user->id))) {
                            return $email->data('model', $user);
                        }
                    }
                    $email->errors('Email address not found');
                }
            });

            if ($input->is_valid()) {

                $user = $input->email->data('model');

                //delete all previous reset keys
                Entity::dao('Neoform\User\Lostpassword')->deleteMulti(
                    new User\Lostpassword\Collection(
                        Entity::dao('Neoform\User\Lostpassword')->by_user($user->id)
                    )
                );

                // Generate random hash
                $hash = Neoform\Type\String\Lib::random_chars(40);

                Entity::dao('Neoform\User\Lostpassword')->insert([
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

            throw $input->exception();
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
                $lost_password = new User\Lostpassword\Model($hash);
            } catch (User\Lostpassword\Exception $e) {
                throw new Neoform\Error\Exception('You password reset link has either expired or is not valid');
            }

            $password      = Neoform\Type\String\Lib::random_chars(8, 12);
            $salt          = User\Lib::generate_salt();
            $password_cost = User\Lib::default_hashmethod_cost();
            $hash_method   = User\Lib::default_hashmethod();
            $user          = $lost_password->user();

            Entity::dao('Neoform\User')->update(
                $user,
                [
                    'password_salt'       => $salt,
                    'password_cost'       => $password_cost,
                    'password_hashmethod' => $hash_method->id,
                    'password_hash'       => $hash_method->hash($password, $salt, $password_cost),
                ]
            );

            Entity::dao('Neoform\User\Lostpassword')->delete($lost_password);

            return [$user, $password];
        }

        public static function insert(array $info) {

            $input = new Input\Collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {
                return Entity::dao('Neoform\User\Lostpassword')->insert([
                    'hash'      => $input->hash->val(),
                    'user_id'   => $input->user_id->val(),
                    'posted_on' => $input->posted_on->val(),
                ]);
            }
            throw $input->exception();
        }

        public static function update(Model $user_lostpassword, array $info, $crush=false) {

            $input = new Input\Collection($info);

            self::_validate_update($user_lostpassword, $input);

            if ($input->is_valid()) {
                return Entity::dao('Neoform\User\Lostpassword')->update(
                    $user_lostpassword,
                    $input->vals(
                        [
                            'hash',
                            'user_id',
                            'posted_on',
                        ],
                        $crush
                    )
                );
            }
            throw $input->exception();
        }

        public static function delete(Model $user_lostpassword) {
            return Entity::dao('Neoform\User\Lostpassword')->delete($user_lostpassword);
        }

        public static function _validate_insert(Input\Collection $input) {

            // hash
            $input->hash->cast('string')->length(1, 40)->callback(function($hash) {
                if (Entity::dao('Neoform\User\Lostpassword')->record($hash->val())) {
                    $hash->errors('already in use');
                }
            });

            // user_id
            $input->user_id->cast('int')->digit(0, 4294967295)->callback(function($user_id) {
                if (Entity::dao('Neoform\User\Lostpassword')->by_user($user_id->val())) {
                    $user_id->errors('already in use');
                }
            })->callback(function($user_id) {
                try {
                    $user_id->data('model', new User\Model($user_id->val()));
                } catch (User\Exception $e) {
                    $user_id->errors($e->getMessage());
                }
            });

            // posted_on
            $input->posted_on->cast('string')->optional()->is_datetime();
        }

        public static function _validate_update(Model $user_lostpassword, Input\Collection $input) {

            // hash
            $input->hash->cast('string')->optional()->length(1, 40)->callback(function($hash) use ($user_lostpassword) {
                $user_lostpassword_info = Entity::dao('Neoform\User\Lostpassword')->record($hash->val());
                if ($user_lostpassword_info && (string) $user_lostpassword_info['hash'] !== $user_lostpassword->hash) {
                    $hash->errors('already in use');
                }
            });

            // user_id
            $input->user_id->cast('int')->optional()->digit(0, 4294967295)->callback(function($user_id) use ($user_lostpassword) {
                $hash_arr = Entity::dao('Neoform\User\Lostpassword')->by_user($user_id->val());
                if (is_array($hash_arr) && $hash_arr && (string) current($hash_arr) !== $user_lostpassword->hash) {
                    $user_id->errors('already in use');
                }
            })->callback(function($user_id) {
                try {
                    $user_id->data('model', new User\Model($user_id->val()));
                } catch (User\Exception $e) {
                    $user_id->errors($e->getMessage());
                }
            });

            // posted_on
            $input->posted_on->cast('string')->optional()->is_datetime();
        }
    }
