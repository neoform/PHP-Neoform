<?php

    namespace Neoform\Input;

    use Neoform\Input\Error;

    class Exception extends \Exception {

        /**
         * @var Error\Collection
         */
        protected $errors;

        /**
         * @param Error\Collection $errors
         */
        public function __construct(Error\Collection $errors) {
            $this->errors = $errors;
        }

        /**
         * @return array
         */
        public function getErrors() {
            return self::_toArray($this->errors);
        }

        /**
         * @param string $k
         *
         * @return string|null
         * @deprecated
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
            foreach ($collection->getAll() as $k => $error) {
                if ($error instanceof Error\Collection) {
                    $arr[$k] = self::_toArray($error);
                } else {
                    $arr[$k] = $error;
                }
            }
            return $arr;
        }
    }