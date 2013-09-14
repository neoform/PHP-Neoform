<?php

    namespace neoform\acl\group\user;

    use neoform\entity;

    /**
     * Acl Group User Model
     *
     * @var int $acl_group_id
     * @var int $user_id
     */
    class model extends entity\link\model implements definition {

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
         * @return \neoform\acl\group\model
         */
        public function acl_group() {
            return $this->_model('acl_group', $this->vars['acl_group_id'], 'acl\group\model');
        }

        /**
         * User Model based on 'user_id'
         *
         * @return \neoform\user\model
         */
        public function user() {
            return $this->_model('user', $this->vars['user_id'], 'user\model');
        }
    }
