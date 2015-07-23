<?php

    namespace Neoform\Input;

    use Neoform\Input\Error;

    class Exception extends \Exception {

        protected $errors;

        /**
         * @param Error\Collection $errors
         */
        public function __construct(Error\Collection $errors) {
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
            return self::_toArray($this->errors);
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
         * @param Error\Collection $collection
         *
         * @return array
         */
        protected static function _toArray(Error\Collection $collection) {
            $arr = [];
            foreach ($collection->all() as $k => $error) {
                if ($error instanceof Error\Collection) {
                    $arr[$k] = self::_toArray($error);
                } else {
                    $arr[$k] = $error;
                }
            }
            return $arr;
        }
    }