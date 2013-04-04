<?php

    /**
    * User Date Model
    *
    * @var int $user_id
    * @var string $created_on
    * @var string $last_login
    * @var string $email_verified_on
    * @var string $password_updated_on
    */
    class user_date_model extends record_model implements user_date_definition {

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
                        return $this->_model($k, $this->vars[$k], 'type_date');

                    default:
                        return $this->vars[$k];
                }
            }

        }

        /**
         * User Model based on 'user_id'
         *
         * @param user_model $user preload model
         *
         * @return user_model
         */
        public function user(user_model $user=null) {
            return $user !== null ? ($this->_vars['user'] = $user) : $this->_model('user', $this->vars['user_id'], 'user_model');
        }

    }
