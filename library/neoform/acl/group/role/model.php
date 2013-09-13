<?php

    namespace neoform\acl\group\role;

    use neoform\entity;

    /**
     * Acl Group Role Model
     *
     * @var int $acl_group_id
     * @var int $acl_role_id
     */
    class model extends entity\link\model implements definition {

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

        /**
         * Acl Group Model based on 'acl_group_id'
         *
         * @return \neoform\acl\group\model
         */
        public function acl_group() {
            return $this->_model('acl_group', $this->vars['acl_group_id'], 'acl\group\model');
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
