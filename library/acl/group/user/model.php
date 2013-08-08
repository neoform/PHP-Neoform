<?php

    /**
    * Acl Group User Model
    *
    * @var int $acl_group_id
    * @var int $user_id
    */
    class acl_group_user_model extends entity_link_model implements acl_group_user_definition {

        public function __get($k) {

            if (isset($this->vars[$k])) {
                switch ($k) {
                    // integers
                    case 'acl_group_id':
                    case 'user_id':
                        return (int) $this->vars[$k];

                    default:
                        return $this->vars[$k];
                }
            }
        }
    }
