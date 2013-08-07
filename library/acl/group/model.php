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
         * Acl Role Collection
         *
         * @return acl_role_collection
         */
        public function acl_role_collection() {
            if (! array_key_exists('acl_role_collection', $this->_vars)) {
                $this->_vars['acl_role_collection'] = new acl_role_collection(
                    entity_dao::get('acl_group_role')->by_acl_group($this->vars['id'])
                );
            }
            return $this->_vars['acl_role_collection'];
        }

        /**
         * User Collection
         *
         * @return user_collection
         */
        public function user_collection() {
            if (! array_key_exists('user_collection', $this->_vars)) {
                $this->_vars['user_collection'] = new user_collection(
                    entity_dao::get('acl_group_user')->by_acl_group($this->vars['id'])
                );
            }
            return $this->_vars['user_collection'];
        }
    }
