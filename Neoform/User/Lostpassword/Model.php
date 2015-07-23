<?php

    namespace Neoform\User\Lostpassword;

    use Neoform\Entity;

    /**
     * User Lostpassword Model
     *
     * @var string $hash
     * @var int $user_id
     * @var datetime $posted_on
     */
    class Model extends Entity\Record\Model implements Definition {

        public function __get($k) {

            if (isset($this->vars[$k])) {
                switch ($k) {
                    // integers
                    case 'user_id':
                        return (int) $this->vars[$k];

                    // dates
                    case 'posted_on':
                        return $this->_model($k, $this->vars[$k], 'Neoform\Type\Date');

                    // strings
                    case 'hash':
                        return (string) $this->vars[$k];

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
