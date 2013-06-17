<?php

    /**
    * Acl Group Model
    *
    * @var int $id
    * @var string $name
    */
    class acl_group_model extends record_model implements acl_group_definition {

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
                    acl_group_role_dao::by_acl_group($this->vars['id'])
                );
            }
            return $this->_vars['acl_group_role_collection'];
        }

        /**
         * Acl Group User Collection
         *
         * @return acl_group_user_collection
         */
        public function acl_group_user_collection() {
            if (! array_key_exists('acl_group_user_collection', $this->_vars)) {
                $this->_vars['acl_group_user_collection'] = new acl_group_user_collection(
                    acl_group_user_dao::by_acl_group($this->vars['id'])
                );
            }
            return $this->_vars['acl_group_user_collection'];
        }
    }
