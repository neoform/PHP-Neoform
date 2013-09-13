<?php

    namespace neoform\user\acl\role;

    use neoform\entity;

    /**
     * User Acl Role Model
     *
     * @var int $user_id
     * @var int $acl_role_id
     */
    class model extends entity\link\model implements definition {

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
         * @return \neoform\user\model
         */
        public function user() {
            return $this->_model('user', $this->vars['user_id'], 'user\model');
        }

        /**
         * Acl Role Model based on 'acl_role_id'
         *
         * @return \neoform\acl\role\model
         */
        public function acl_role() {
            return $this->_model('acl_role', $this->vars['acl_role_id'], 'acl\role\model');
        }
    }
