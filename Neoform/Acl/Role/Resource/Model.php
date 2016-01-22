<?php

    namespace Neoform\Acl\Role\Resource;

    use Neoform\Entity;

    /**
     * Acl Role Resource Model
     *
     * @var int $acl_role_id
     * @var int $acl_resource_id
     */
    class Model extends Entity\Link\Model {

        // Load entity details into the class
        use Details;

        public function __get($k) {

            if (isset($this->vars[$k])) {
                switch ($k) {
                    // integers
                    case 'acl_role_id':
                    case 'acl_resource_id':
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
                    case 'acl_role_id':
                    case 'acl_resource_id':
                        return (int) $this->vars[$k];

                    default:
                        return $this->vars[$k];
                }
            }
        }

        /**
         * Acl Role Model based on 'acl_role_id'
         *
         * @return \Neoform\Acl\Role\Model
         */
        public function acl_role() {
            return $this->_model('acl_role', $this->vars['acl_role_id'], 'Neoform\Acl\Role\Model');
        }

        /**
         * Acl Resource Model based on 'acl_resource_id'
         *
         * @return \Neoform\Acl\Resource\Model
         */
        public function acl_resource() {
            return $this->_model('acl_resource', $this->vars['acl_resource_id'], 'Neoform\Acl\Resource\Model');
        }
    }
