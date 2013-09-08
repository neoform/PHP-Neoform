<?php

    namespace neoform;

    /**
     * Acl Group User Model
     *
     * @var int $acl_group_id
     * @var int $user_id
     */
    class acl_group_user_model extends entity_link_model implements acl_group_user_definition {

        public function __get($k) {

            if (isset($this->vars[$k])) {
                switch ($k) {
                    // integers
                    case 'acl_group_id':
                    case 'user_id':
                        return (int) $this->vars[$k];

                    default:
                        return $this->vars[$k];
                }
            }
        }

        /**
         * Acl Group Model based on 'acl_group_id'
         *
         * @return acl_group_model
         */
        public function acl_group() {
            return $this->_model('acl_group', $this->vars['acl_group_id'], 'acl_group_model');
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
