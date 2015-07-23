<?php

    namespace Neoform\User\Date;

    use Neoform\Entity;

    /**
     * User Date Model
     *
     * @var int $user_id
     * @var datetime $created_on
     * @var datetime|null $last_login
     * @var datetime|null $email_verified_on
     * @var datetime|null $password_updated_on
     */
    class Model extends Entity\Record\Model implements Definition {

        public function __get($k) {

            if (isset($this->vars[$k])) {
                switch ($k) {
                    // integers
                    case 'user_id':
                        return (int) $this->vars[$k];

                    // dates
                    case 'created_on':
                    case 'last_login':
                    case 'email_verified_on':
                    case 'password_updated_on':
                        return $this->_model($k, $this->vars[$k], 'Neoform\Type\Date');

                    default:
                        return $this->vars[$k];
                }
            }
        }

        /**
         * User Model based on 'user_id'
         *
         * @return \Neoform\User\Model
         */
        public function user() {
            return $this->_model('user', $this->vars['user_id'], 'Neoform\User\Model');
        }
    }
