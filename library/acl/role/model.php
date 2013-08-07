<?php

    /**
    * Acl Role Model
    *
    * @var int $id
    * @var string $name
    */
    class acl_role_model extends entity_record_model implements acl_role_definition {

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
         * Acl Group Collection
         *
         * @return acl_group_collection
         */
        public function acl_group_collection() {
            if (! array_key_exists('acl_group_collection', $this->_vars)) {
                $this->_vars['acl_group_collection'] = new acl_group_collection(
                    entity_dao::get('acl_group_role')->by_acl_role($this->vars['id'])
                );
            }
            return $this->_vars['acl_group_collection'];
        }

        /**
         * Acl Resource Collection
         *
         * @return acl_resource_collection
         */
        public function acl_resource_collection() {
            if (! array_key_exists('acl_resource_collection', $this->_vars)) {
                $this->_vars['acl_resource_collection'] = new acl_resource_collection(
                    entity_dao::get('acl_role_resource')->by_acl_role($this->vars['id'])
                );
            }
            return $this->_vars['acl_resource_collection'];
        }

        /**
         * User Collection
         *
         * @return user_collection
         */
        public function user_collection() {
            if (! array_key_exists('user_collection', $this->_vars)) {
                $this->_vars['user_collection'] = new user_collection(
                    entity_dao::get('user_acl_role')->by_acl_role($this->vars['id'])
                );
            }
            return $this->_vars['user_collection'];
        }
    }
