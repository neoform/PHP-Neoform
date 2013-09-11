<?php

    namespace neoform\input;

    class exception extends \exception {

        protected $errors;

        public function __construct(error\collection $errors) {
            $this->errors = $errors;
        }

        public function message() {
            return $this->getMessage();
        }

        public function errors() {
            return self::_to_array($this->errors);
        }

        public function __get($k) {
            if (isset($this->errors[$k])) {
                return $this->errors[$k];
            }
        }

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