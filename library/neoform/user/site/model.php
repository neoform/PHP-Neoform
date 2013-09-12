<?php

    namespace neoform\user\site;

    use neoform\entity;

    /**
     * User Site Model
     *
     * @var int $user_id
     * @var int $site_id
     */
    class model extends entity\link\model implements definition {

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
         * User Model based on 'user_id'
         *
         * @return \neoform\user\model
         */
        public function user() {
            return $this->_model('user', $this->vars['user_id'], '\neoform\user\model');
        }

        /**
         * Site Model based on 'site_id'
         *
         * @return \neoform\site\model
         */
        public function site() {
            return $this->_model('site', $this->vars['site_id'], '\neoform\site\model');
        }
    }
