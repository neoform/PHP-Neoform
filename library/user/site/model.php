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
         * @param site_model $site preload model
         *
         * @return site_model
         */
        public function site(site_model $site=null) {
            return $site !== null ? ($this->_vars['site'] = $site) : $this->_model('site', $this->vars['site_id'], 'site_model');
        }

        /**
         * User Model based on 'user_id'
         *
         * @param user_model $user preload model
         *
         * @return user_model
         */
        public function user(user_model $user=null) {
            return $user !== null ? ($this->_vars['user'] = $user) : $this->_model('user', $this->vars['user_id'], 'user_model');
        }

    }
