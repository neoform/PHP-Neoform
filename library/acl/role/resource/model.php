<?php

    /**
     * Acl Role Resource Model
     *
     * @var int $acl_role_id
     * @var int $acl_resource_id
     */
    class acl_role_resource_model extends entity_link_model implements acl_role_resource_definition {

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
         * @return acl_role_model
         */
        public function acl_role() {
            return $this->_model('acl_role', $this->vars['acl_role_id'], 'acl_role_model');
        }

        /**
         * Acl Resource Model based on 'acl_resource_id'
         *
         * @return acl_resource_model
         */
        public function acl_resource() {
            return $this->_model('acl_resource', $this->vars['acl_resource_id'], 'acl_resource_model');
        }
    }
