<?php

    namespace Neoform\Auth;

    use Neoform\Input;
    use Neoform\Entity;
    use Neoform;

    class Api {

        /**
         * Creates a Auth model with $info
         *
         * @param Neoform\Input\Collection $input
         *
         * @return Model
         * @throws Input\Exception
         */
        public static function insert(Neoform\Input\Collection $input) {

            // Make sure validation has been applied
            if (! $input->isValidatedEntries([ 'hash', 'user_id', 'expires_on', ])) {
                $input->applyValidation(new Validator\Insert);
            }

            // If input did not pass validation
            if (! $input->isValid()) {
                throw $input->getException();
            }

            return Dao::get()->insert(
                $input->getVals([
                    'hash',
                    'user_id',
                    'expires_on',
                ])
            );
        }

        /**
         * Update a Auth model with $info
         *
         * @param Model                    $auth
         * @param Neoform\Input\Collection $input
         * @param bool                     $includeEmpty
         *
         * @return Model
         * @throws Input\Exception
         */
        public static function update(Model $auth, Neoform\Input\Collection $input, $includeEmpty=false) {

            // Make sure validation has been applied
            if (! $input->isValidatedEntries([ 'hash', 'user_id', 'expires_on', ])) {
                $input->applyValidation(new Validator\Update($auth, $includeEmpty));
            }

            // If input did not pass validation
            if (! $input->isValid()) {
                throw $input->getException();
            }

            return Dao::get()->update(
                $auth,
                $input->getVals(
                    [
                        'hash',
                        'user_id',
                        'expires_on',
                    ],
                    $includeEmpty
                )
            );
        }

        /**
         * @param Neoform\Session\Auth $auth
         * @param Neoform\Site\Model   $site
         * @param array                $info
         *
         * @return Model
         * @throws Input\Exception
         */
        public static function verifyLogin(Neoform\Session\Auth $auth, Neoform\Site\Model $site, array $info) {

            $input = new Input\Collection($info);

            $attemtpedUser = null;

            $input->validate('email', 'string')
                ->trim()
                ->toLower()
                ->requireLength(1, 255)
                ->isEmail()
                ->callback(function(Input\Input $email) use (& $attemtpedUser, $site) {
                    try {
                        if ($user_id = current(Neoform\User\Dao::get()->by_email($email->getVal()))) {
                            if (count(Neoform\User\Site\Dao::get()->by_site_user($site->id, $user_id))) {
                                $attemtpedUser = \Neoform\User\Model::fromPk($user_id);
                                return;
                            }
                        }
                    } catch (Neoform\User\Exception $e) {
    
                    }
                    $email->setErrors('Your email address or password is incorrect.');
                });
            
            $input->validate('remember', 'bool');
            
            $input->validate('password', 'string')
                ->callback(function(Input\Input $password) use ($attemtpedUser) {
                    // Verify password matches
                    if ($attemtpedUser && ! Neoform\User\Lib::password_matches($attemtpedUser, $password->getVal())) {
                        $password->setErrors('Your email address or password is incorrect.');
    
                    // Make sure account is active
                    } else if ($attemtpedUser && ! $attemtpedUser->is_active()) {
                        $password->setErrors('You cannot log in with this account at this time');
                    }
                });

            if (! $input->isValid()) {
                throw $input->getException();
            }
            
            $auth->setUser(
                $attemtpedUser,
                $input->remember->getVal()
            );

            return true;
        }
    }
