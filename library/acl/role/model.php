<?php

    /**
    * Acl Role Model
    *
    * @var int $id
    * @var string $name
    */
    class acl_role_model extends record_model implements acl_role_definition {

        public function __get($k) {

            if (isset($this->vars[$k])) {
                switch ($k) {
                    // integers
                    case 'id':
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
         * Acl Group Role Collection
         *
         * @return acl_group_role_collection
         */
        public function acl_group_role_collection() {
            if (! array_key_exists('acl_group_role_collection', $this->_vars)) {
                $this->_vars['acl_group_role_collection'] = new acl_group_role_collection(
                    acl_group_role_dao::by_acl_role($this->vars['id'])
                );
            }
            return $this->_vars['acl_group_role_collection'];
        }

        /**
         * Acl Role Resource Collection
         *
         * @return acl_role_resource_collection
         */
        public function acl_role_resource_collection() {
            if (! array_key_exists('acl_role_resource_collection', $this->_vars)) {
                $this->_vars['acl_role_resource_collection'] = new acl_role_resource_collection(
                    acl_role_resource_dao::by_acl_role($this->vars['id'])
                );
            }
            return $this->_vars['acl_role_resource_collection'];
        }

        /**
         * User Acl Role Collection
         *
         * @return user_acl_role_collection
         */
        public function user_acl_role_collection() {
            if (! array_key_exists('user_acl_role_collection', $this->_vars)) {
                $this->_vars['user_acl_role_collection'] = new user_acl_role_collection(
                    user_acl_role_dao::by_role($this->vars['id'])
                );
            }
            return $this->_vars['user_acl_role_collection'];
        }
    }
