<?php

    namespace Neoform\Request;

    abstract class Parameters {

        /**
         * @var array
         */
        protected $vals = [];

        /**
         * @param array $vals
         */
        public function __construct(array $vals) {
            $this->vals = $vals;
        }

        /**
         * @param string|integer $k
         *
         * @return string|null
         */
        public function __get($k) {
            return isset($this->vals[$k]) ? $this->vals[$k] : null;
        }

        /**
         * @param string|integer $k
         *
         * @return string|null
         */
        public function get($k) {
            return isset($this->vals[$k]) ? $this->vals[$k] : null;
        }

        /**
         * @param string $k
         *
         * @return bool
         */
        public function exists($k) {
            return array_key_exists($k, $this->vals);
        }

        /**
         * @return array
         */
        public function toArray() {
            return $this->vals;
        }

        /**
         * @return int
         */
        public function count() {
            return count($this->vals);
        }
    }