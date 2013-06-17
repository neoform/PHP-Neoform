<?php

    /**
    * User Acl Role Model
    *
    * @var int $user_id
    * @var int $role_id
    */
    class user_acl_role_model extends link_model implements user_acl_role_definition {

        public function __get($k) {

            if (isset($this->vars[$k])) {
                switch ($k) {
                    // integers
                    case 'user_id':
                    case 'role_id':
                        return (int) $this->vars[$k];

                    default:
                        return $this->vars[$k];
                }
            }
        }
    }
