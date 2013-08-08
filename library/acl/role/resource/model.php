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
    }
