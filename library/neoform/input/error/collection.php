<?php

    namespace neoform;

    use ArrayObject;

    class input_error_collection extends ArrayObject {

        public function __construct(array $errors = null) {
            if ($errors) {
                $this->exchangeArray($errors);
            }
        }

        public function __get($k) {
            if (isset($this[$k])) {
                return $this[$k];
            }
        }

        public function __set($k, $v) {
            $this[$k] = $v;
        }

        public function all() {
            return (array) $this;
        }

        public function count() {
            return \count($this);
        }
    }