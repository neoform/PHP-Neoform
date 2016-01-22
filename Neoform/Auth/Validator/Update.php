<?php

    namespace Neoform\Auth\Validator;

    use Neoform;

    class Update implements Neoform\Input\Validator {

        /**
         * @var Neoform\Auth\Model
         */
        protected $auth;

        /**
         * @var bool
         */
        protected $includeEmpty;

        /**
         * @param Neoform\Auth\Model $auth
         * @param bool               $includeEmpty
         */
        public function __construct(Neoform\Auth\Model $auth, $includeEmpty) {
            $this->auth         = $auth;
            $this->includeEmpty = $includeEmpty;
        }

        /**
         * Validate for update
         *
         * @param Neoform\Input\Collection $input
         */
        public function validate(Neoform\Input\Collection $input) {

            /**
             * Hash [binary]
             */
            $input->validate('hash', 'binary', !$this->includeEmpty)
                ->requireLength(40, 40)
                ->callback(function(Neoform\Input\Input $hash) {
                    $auth_info = Neoform\Auth\Dao::get()->record($hash->getVal());
                    if ($auth_info && (binary) $auth_info['hash'] !== $this->auth->hash) {
                        $hash->setErrors('already in use');
                    }
                });

            /**
             * User ID [int]
             */
            $input->validate('user_id', 'int', !$this->includeEmpty)
                ->requireDigit(1, 4294967295)
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
            $input->validate('expires_on', 'string', !$this->includeEmpty)
                ->isDateTime();
        }
    }
