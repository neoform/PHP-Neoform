<?php

    namespace neoform\acl\role\resource;

    use neoform\entity;

    /**
     * Acl Role Resource Model
     *
     * @var int $acl_role_id
     * @var int $acl_resource_id
     */
    class model extends entity\link\model implements definition {

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

        /**
         * Acl Role Model based on 'acl_role_id'
         *
         * @return \neoform\acl\role\model
         */
        public function acl_role() {
            return $this->_model('acl_role', $this->vars['acl_role_id'], '\neoform\acl\role\model');
        }

        /**
         * Acl Resource Model based on 'acl_resource_id'
         *
         * @return \neoform\acl\resource\model
         */
        public function acl_resource() {
            return $this->_model('acl_resource', $this->vars['acl_resource_id'], '\neoform\acl\resource\model');
        }
    }
