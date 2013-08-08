<?php

    /**
    * User Hashmethod Model
    *
    * @var int $id
    * @var string $name
    */
    class user_hashmethod_model extends entity_record_model implements user_hashmethod_definition {

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
                    entity::dao('user')->by_password_hashmethod($this->vars['id'])
                );
            }
            return $this->_vars['user_collection'];
        }

        /**
         * Hashes a password, with salt given a certain cost value
         *
         * @param string        $password
         * @param binary|string $salt
         * @param integer       $cost
         *
         * @return binary|string
         * @throws user_exception
         */
        public function hash($password, $salt, $cost) {
            if (($cost = (int) $cost) < 1) {
                throw new user_exception('Password hash cost must be at least 1');
            }

            // Seems pointless to make an object here, except PHP doesn't allow abstract static functions, weak
            $hashmethod = "user_hashmethod_driver_{$this->name}";
            $hashmethod = new $hashmethod;
            return $hashmethod->hash($password, $salt, $cost);
        }
    }
