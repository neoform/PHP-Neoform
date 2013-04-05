<?php

    /**
    * User Permission Model
    *
    * @var int $user_id
    * @var int $permission_id
    */
    class user_permission_model extends link_model implements user_permission_definition {

        public function __get($k) {

            if (isset($this->vars[$k])) {
                switch ($k) {
                    // integers
                    case 'user_id':
                    case 'permission_id':
                        return (int) $this->vars[$k];

                    default:
                        return $this->vars[$k];
                }
            }

        }

        /**
         * Permission Model based on 'permission_id'
         *
         * @return permission_model
         */
        public function permission() {
            return $this->_model('permission', $this->vars['permission_id'], 'permission_model');
        }

        /**
         * User Model based on 'user_id'
         *
         * @return user_model
         */
        public function user() {
            return $this->_model('user', $this->vars['user_id'], 'user_model');
        }

    }
