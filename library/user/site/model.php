<?php

    /**
    * User Site Model
    *
    * @var int $user_id
    * @var int $site_id
    */
    class user_site_model extends link_model implements user_site_definition {

        public function __get($k) {

            if (isset($this->vars[$k])) {
                switch ($k) {
                    // integers
                    case 'user_id':
                    case 'site_id':
                        return (int) $this->vars[$k];

                    default:
                        return $this->vars[$k];
                }
            }

        }

        /**
         * Site Model based on 'site_id'
         *
         * @return site_model
         */
        public function site() {
            return $this->_model('site', $this->vars['site_id'], 'site_model');
        }

        /**
         * User Model based on 'user_id'
         *
         * @return user_model
         */
        public function user() {
            return $this->_model('user', $this->vars['user_id'], 'user_model');
        }

    }
