<?php

    /**
    * User Hashmethod Model
    *
    * @var int $id
    * @var string $name
    */
    class user_hashmethod_model extends record_model implements user_hashmethod_definition {

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
         * @return user_collection
         */
        public function user_collection() {
            if (! array_key_exists('user_collection', $this->_vars)) {
                $this->_vars['user_collection'] = new user_collection(
                    user_dao::by_password_hashmethod($this->vars['id'])
                );
            }
            return $this->_vars['user_collection'];
        }

    }
