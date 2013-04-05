<?php

    /**
    * Auth Model
    *
    * @var string $hash
    * @var int $user_id
    * @var string $expires_on
    */
    class auth_model extends record_model implements auth_definition {

        use core_instance;

        public function __get($k) {

            if (isset($this->vars[$k])) {
                switch ($k) {
                    // integers
                    case 'user_id':
                        return (int) $this->vars[$k];

                    // dates
                    case 'expires_on':
                        return $this->_model($k, $this->vars[$k], 'type_date');

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
         * @return user_model
         */
        public function user() {
            return $this->_model('user', $this->vars['user_id'], 'user_model');
        }

        /**
         * Instantiate models based on the user_id
         *
         * @param string $name
         * @param mixed $args
         *
         * @return mixed|record_model
         */
        public function __call($name, $args) {
            if (isset($this->vars['user_id'])) {
                return $this->_model($name, $this->vars['user_id'], $name . '_model');
            }
        }
    }
