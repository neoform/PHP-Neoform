<?php

    namespace Neoform\User\Acl\Role;

    use Neoform\Entity;

    /**
     * User Acl Role Model
     *
     * @var int $user_id
     * @var int $acl_role_id
     */
    class Model extends Entity\Link\Model implements Definition {

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
         * @return \Neoform\User\Model
         */
        public function user() {
            return $this->_model('user', $this->vars['user_id'], 'Neoform\User\Model');
        }

        /**
         * Acl Role Model based on 'acl_role_id'
         *
         * @return \Neoform\Acl\Role\Model
         */
        public function acl_role() {
            return $this->_model('acl_role', $this->vars['acl_role_id'], 'Neoform\Acl\Role\Model');
        }
    }
