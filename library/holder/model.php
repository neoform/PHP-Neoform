<?php

    abstract class holder_model {

        protected $vars;
        protected $_vars = []; // calculated fields

        public function __construct(array $info=null) {
            $this->vars = $info;
        }

        public function __get($k) {
            if (isset($this->vars[$k])) {
                return $this->vars[$k];
            }
        }

        public function _update(array $vars) {
            //clean the temp vars
            $this->_vars = [];

            //apply the new vars
            foreach ($vars as $k => $v) {
                $this->vars[$k] = $v;
            }
        }

        public function __sleep() {
            return [
                'vars',
            ];
        }

        public function export(array $fields=null) {
            if ($fields !== null && count($fields)) {
                return array_intersect_key($this->vars, array_flip($fields));
            } else {
                return $this->vars;
            }
        }

        protected function _model($key, $id, $model, $default=null) {
            if (! array_key_exists($key, $this->_vars)) {
                if ($id !== null) {
                    $this->_vars[$key] = new $model($id);
                } else {
                    $this->_vars[$key] = $default;
                }
            }
            return $this->_vars[$key];
        }
    }
