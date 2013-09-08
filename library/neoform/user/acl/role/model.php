<?php

    namespace neoform;

    /**
     * User Acl Role Model
     *
     * @var int $user_id
     * @var int $acl_role_id
     */
    class user_acl_role_model extends entity_link_model implements user_acl_role_definition {

        public function __get($k) {

            if (isset($this->vars[$k])) {
                switch ($k) {
                    // integers
                    case 'user_id':
                    case 'acl_role_id':
                        return (int) $this->vars[$k];

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
         * Acl Role Model based on 'acl_role_id'
         *
         * @return acl_role_model
         */
        public function acl_role() {
            return $this->_model('acl_role', $this->vars['acl_role_id'], 'acl_role_model');
        }
    }
