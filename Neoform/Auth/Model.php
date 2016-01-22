<?php

    namespace Neoform\Auth;

    use DateTime;
    use Neoform\Entity;

    /**
     * Auth Model
     *
     * @var binary $hash
     * @var int $user_id
     * @var DateTime $expires_on
     */
    class Model extends Entity\Record\Model {

        // Load entity details into the class
        use Details;

        public function __get($k) {

            if (isset($this->vars[$k])) {
                switch ($k) {
                    // integers
                    case 'user_id':
                        return (int) $this->vars[$k];

                    // dates
                    case 'expires_on':
                        return $this->_model($k, $this->vars[$k], 'Neoform\Type\Date');

                    // strings
                    case 'hash':
                        return (string) $this->vars[$k];

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
                        return (int) $this->vars[$k];

                    // dates
                    case 'expires_on':
                        return $this->_model($k, $this->vars[$k], 'Neoform\Type\Date');

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
         * @return mixed|Entity\Record\Model
         */
        public function __call($name, $args) {
            if (isset($this->vars['user_id'])) {
                return $this->_model($name, $this->vars['user_id'], str_replace('_', '\\', $name) . '\Model');
            }
        }

        /**
         * Instantiate models based on the user_id
         *
         * @return mixed|Entity\Record\Model
         */
        public function getUser() {
            if (isset($this->vars['user_id'])) {
                return $this->_model('user', $this->vars['user_id'], 'Neoform\User\Model');
            }
        }

        /**
         * Checks if user is logged in
         *
         * @return bool
         */
        public function loggedIn() {
            return (bool) $this->user_id;
        }
    }
