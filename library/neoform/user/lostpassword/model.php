<?php

    namespace neoform\user\lostpassword;

    use neoform\entity;

    /**
     * User Lostpassword Model
     *
     * @var string $hash
     * @var int $user_id
     * @var datetime $posted_on
     */
    class model extends entity\record\model implements definition {

        public function __get($k) {

            if (isset($this->vars[$k])) {
                switch ($k) {
                    // integers
                    case 'user_id':
                        return (int) $this->vars[$k];

                    // dates
                    case 'posted_on':
                        return $this->_model($k, $this->vars[$k], 'type\date');

                    // strings
                    case 'hash':
                        return (string) $this->vars[$k];

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
            return $this->_model('user', $this->vars['user_id'], 'user\model');
        }
    }
