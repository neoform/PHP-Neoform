<?php

    /**
    * Acl Resource Model
    *
    * @var int $id
    * @var int|null $parent_id
    * @var string $name
    */
    class acl_resource_model extends record_model implements acl_resource_definition {

        public function __get($k) {

            if (isset($this->vars[$k])) {
                switch ($k) {
                    // integers
                    case 'id':
                    case 'parent_id':
                        return (int) $this->vars[$k];

                    // strings
                    case 'name':
                        return (string) $this->vars[$k];

                    default:
                        return $this->vars[$k];
                }
            }
        }

        /**
         * Child Acl Resource Collection
         *
         * @return acl_resource_collection
         */
        public function child_acl_resource_collection() {
            if (! array_key_exists('child_acl_resource_collection', $this->_vars)) {
                $this->_vars['child_acl_resource_collection'] = new acl_resource_collection(
                    acl_resource_dao::by_parent($this->vars['id'])
                );
            }
            return $this->_vars['child_acl_resource_collection'];
        }

        /**
         * Acl Role Collection
         *
         * @return acl_role_collection
         */
        public function acl_role_collection() {
            if (! array_key_exists('acl_role_collection', $this->_vars)) {
                $this->_vars['acl_role_collection'] = new acl_role_collection(
                    acl_role_resource_dao::by_acl_resource($this->vars['id'])
                );
            }
            return $this->_vars['acl_role_collection'];
        }

        /**
         * Parent Acl Resource Model based on 'parent_id'
         *
         * @return acl_resource_model
         */
        public function parent_acl_resource() {
            return $this->_model('parent_acl_resource', $this->vars['parent_id'], 'acl_resource_model');
        }
    }
