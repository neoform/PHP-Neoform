<?php

    namespace Neoform\Input\Error;

    use ArrayObject;

    class Collection extends ArrayObject {

        /**
         * @param String[]|null $errors
         */
        public function __construct(array $errors = null) {
            if ($errors) {
                $this->exchangeArray($errors);
            }
        }

        /**
         * @param $k
         *
         * @return string
         */
        public function __get($k) {
            if (isset($this[$k])) {
                return $this[$k];
            }
        }

        /**
         * @param string $k
         * @param string $v
         */
        public function __set($k, $v) {
            $this[$k] = $v;
        }

        /**
         * @return array
         * @deprecated
         */
        public function all() {
            return (array) $this;
        }

        /**
         * @return array
         */
        public function getAll() {
            return (array) $this;
        }
    }