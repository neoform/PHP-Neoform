<?php

    namespace Neoform\Acl\Group\User;

    use Neoform\Entity;

    /**
     * Acl Group User Model
     *
     * @var int $acl_group_id
     * @var int $user_id
     */
    class Model extends Entity\Link\Model {

        // Load entity details into the class
        use Details;

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

        public function get($k) {

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
         * @return \Neoform\Acl\Group\Model
         */
        public function acl_group() {
            return $this->_model('acl_group', $this->vars['acl_group_id'], 'Neoform\Acl\Group\Model');
        }

        /**
         * User Model based on 'user_id'
         *
         * @return \Neoform\User\Model
         */
        public function user() {
            return $this->_model('user', $this->vars['user_id'], 'Neoform\User\Model');
        }
    }
