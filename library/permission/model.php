<?php

    /**
    * Permission Model
    *
    * @var int $id
    * @var string $name
    */
    class permission_model extends record_model implements permission_definition {

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
         * User Collection
         *
         * @param user_collection $user_collection preload collection
         *
         * @return user_collection
         */
        public function user_collection(user_collection $user_collection=null) {
            if (! array_key_exists('user_collection', $this->_vars)) {
                if ($user_collection !== null) {
                    $this->_vars['user_collection'] = $user_collection;
                } else {
                    $this->_vars['user_collection'] = new user_collection(
                        user_permission_dao::by_permission($this->vars['id'])
                    );
                }
            }
            return $this->_vars['user_collection'];
        }

    }
