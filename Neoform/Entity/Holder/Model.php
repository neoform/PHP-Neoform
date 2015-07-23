<?php

    namespace Neoform\Holder;

    abstract class Model {

        protected $vars;
        protected $_vars = []; // calculated fields

        /**
         * @param array $info
         */
        public function __construct(array $info=null) {
            $this->vars = $info;
        }

        /**
         * @param string $k
         *
         * @return mixed
         */
        public function __get($k) {
            if (isset($this->vars[$k])) {
                return $this->vars[$k];
            }
        }

        /**
         * Update model data
         *
         * @param array $vars
         */
        public function _update(array $vars) {
            //clean the temp vars
            $this->_vars = [];

            //apply the new vars
            foreach ($vars as $k => $v) {
                $this->vars[$k] = $v;
            }
        }

        /**
         * @return array
         */
        public function __sleep() {
            return [
                'vars',
            ];
        }

        /**
         * Export part or all of the model data as an array
         *
         * @param array $fields
         *
         * @return array
         */
        public function export(array $fields=null) {
            if ($fields) {
                return array_intersect_key($this->vars, array_flip($fields));
            } else {
                return $this->vars;
            }
        }

        /**
         * @param string         $key
         * @param string|integer $pk
         * @param string         $model_name
         * @param mixed|null     $default
         *
         * @return model|$default
         */
        protected function _model($key, $pk, $model_name, $default=null) {
            if (! array_key_exists($key, $this->_vars)) {
                if ($pk !== null) {
                    $this->_vars[$key] = new $model_name($pk);
                } else {
                    $this->_vars[$key] = $default;
                }
            }
            return $this->_vars[$key];
        }
    }
