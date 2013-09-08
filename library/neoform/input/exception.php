<?php

    namespace neoform;

    class input_exception extends \exception {

        protected $errors;

        public function __construct(input_error_collection $errors) {
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

        protected static function _to_array(input_error_collection $collection) {
            $arr = [];
            foreach ($collection->all() as $k => $error) {
                if ($error instanceof input_error_collection) {
                    $arr[$k] = self::_to_array($error);
                } else {
                    $arr[$k] = $error;
                }
            }
            return $arr;
        }
    }