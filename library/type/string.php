<?php

    class type_string {
        protected $val;

        public function __construct($val) {
            $this->val = $val;
        }

        public function unescaped() {
            return $this->val;
        }

        public function escaped() {
            return htmlspecialchars($this->val);
        }

        public function __tostring() {
            return htmlspecialchars($this->val);
        }
    }