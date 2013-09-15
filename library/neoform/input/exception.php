<?php

    namespace neoform\input;

    use neoform\input\error;

    class exception extends \exception {

        protected $errors;

        /**
         * @param error\collection $errors
         */
        public function __construct(error\collection $errors) {
            $this->errors = $errors;
        }

        /**
         * @return string
         */
        public function message() {
            return $this->getMessage();
        }

        /**
         * @return array
         */
        public function errors() {
            return self::_to_array($this->errors);
        }

        /**
         * @param string $k
         *
         * @return mixed
         */
        public function __get($k) {
            if (isset($this->errors[$k])) {
                return $this->errors[$k];
            }
        }

        /**
         * @param error\collection $collection
         *
         * @return array
         */
        protected static function _to_array(error\collection $collection) {
            $arr = [];
            foreach ($collection->all() as $k => $error) {
                if ($error instanceof error\collection) {
                    $arr[$k] = self::_to_array($error);
                } else {
                    $arr[$k] = $error;
                }
            }
            return $arr;
        }
    }