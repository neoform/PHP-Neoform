<?php

    namespace Neoform\Auth\Validator;

    use Neoform;

    class Insert implements Neoform\Input\Validator {

        /**
         * Validate for insert
         *
         * @param Neoform\Input\Collection $input
         */
        public function validate(Neoform\Input\Collection $input) {

            /**
             * Hash [binary]
             */
            $input->validate('hash', 'binary')
                ->requireLength(1, 40)
                ->callback(function(Neoform\Input\Input $hash) {
                    if (Neoform\Auth\Dao::get()->record($hash->getVal())) {
                        $hash->setErrors('already in use');
                    }
                });

            /**
             * User ID [int]
             */
            $input->validate('user_id', 'int')
                ->requireDigit(0, 4294967295)
                ->callback(function(Neoform\Input\Input $userId) {
                    try {
                        $userId->setData('model', Neoform\User\Model::fromPk($userId->getVal()));
                    } catch (Neoform\User\Exception $e) {
                        $userId->setErrors($e->getMessage());
                    }
                });

            /**
             * Expires On [string]
             */
            $input->validate('expires_on', 'string', true)
                ->isDateTime();
        }
    }
