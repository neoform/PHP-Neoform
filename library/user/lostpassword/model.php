<?php

    /**
    * User Lostpassword Model
    *
    * @var string $hash
    * @var int $user_id
    * @var string $posted_on
    */
    class user_lostpassword_model extends record_model implements user_lostpassword_definition {

        public function __get($k) {

            if (isset($this->vars[$k])) {
                switch ($k) {
                    // integers
                    case 'user_id':
                        return (int) $this->vars[$k];

                    // dates
                    case 'posted_on':
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
         * @param user_model $user preload model
         *
         * @return user_model
         */
        public function user(user_model $user=null) {
            return $user !== null ? ($this->_vars['user'] = $user) : $this->_model('user', $this->vars['user_id'], 'user_model');
        }

    }
