<?php

    namespace neoform\auth;

    use neoform\entity;

    /**
     * Auth Model
     *
     * @var binary $hash
     * @var int $user_id
     * @var datetime $expires_on
     */
    class model extends entity\record\model implements definition {

        public function __get($k) {

            if (isset($this->vars[$k])) {
                switch ($k) {
                    // integers
                    case 'user_id':
                        return (int) $this->vars[$k];

                    // dates
                    case 'expires_on':
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
         * Instantiate models based on the user_id
         *
         * @param string $name
         * @param mixed $args
         *
         * @return mixed|entity\record\model
         */
        public function __call($name, $args) {
            if (isset($this->vars['user_id'])) {
                return $this->_model($name, $this->vars['user_id'], str_replace('_', '\\', $name) . '\model');
            }
        }

        /**
         * Checks if user is logged in
         *
         * @return bool
         */
        public function logged_in() {
            return (bool) $this->user_id;
        }
    }
