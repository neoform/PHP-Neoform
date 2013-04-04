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
         * @param permission_model $permission preload model
         *
         * @return permission_model
         */
        public function permission(permission_model $permission=null) {
            return $permission !== null ? ($this->_vars['permission'] = $permission) : $this->_model('permission', $this->vars['permission_id'], 'permission_model');
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
