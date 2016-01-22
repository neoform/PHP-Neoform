<?php

    namespace Neoform\Acl\Group\Role;

    use Neoform\Entity;

    /**
     * Acl Group Role Model
     *
     * @var int $acl_group_id
     * @var int $acl_role_id
     */
    class Model extends Entity\Link\Model {

        // Load entity details into the class
        use Details;

        public function __get($k) {

            if (isset($this->vars[$k])) {
                switch ($k) {
                    // integers
                    case 'acl_group_id':
                    case 'acl_role_id':
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
                    case 'acl_role_id':
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
         * Acl Role Model based on 'acl_role_id'
         *
         * @return \Neoform\Acl\Role\Model
         */
        public function acl_role() {
            return $this->_model('acl_role', $this->vars['acl_role_id'], 'Neoform\Acl\Role\Model');
        }
    }
