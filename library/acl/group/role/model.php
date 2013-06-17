<?php

    /**
    * Acl Group Role Model
    *
    * @var int $acl_group_id
    * @var int $acl_role_id
    */
    class acl_group_role_model extends link_model implements acl_group_role_definition {

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
    }
