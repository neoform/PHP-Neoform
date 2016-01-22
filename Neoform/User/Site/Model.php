<?php

    namespace Neoform\User\Site;

    use Neoform\Entity;

    /**
     * User Site Model
     *
     * @var int $user_id
     * @var int $site_id
     */
    class Model extends Entity\Link\Model {

        // Load entity details into the class
        use Details;

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

        public function get($k) {

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
         * @return \Neoform\User\Model
         */
        public function user() {
            return $this->_model('user', $this->vars['user_id'], 'Neoform\User\Model');
        }

        /**
         * Site Model based on 'site_id'
         *
         * @return \Neoform\Site\Model
         */
        public function site() {
            return $this->_model('site', $this->vars['site_id'], 'Neoform\Site\Model');
        }
    }
