<?php

    /**
     * Auth Model
     *
     * @var binary $hash
     * @var int $user_id
     * @var datetime $expires_on
     */
    class auth_model extends entity_record_model implements auth_definition {

        public function __get($k) {

            if (isset($this->vars[$k])) {
                switch ($k) {
                    // integers
                    case 'user_id':
                        return (int) $this->vars[$k];

                    // dates
                    case 'expires_on':
                        return $this->_model($k, $this->vars[$k], 'type_date');

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
         * @return mixed|entity_record_model
         */
        public function __call($name, $args) {
            if (isset($this->vars['user_id'])) {
                return $this->_model($name, $this->vars['user_id'], "{$name}_model");
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
