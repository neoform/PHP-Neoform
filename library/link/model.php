<?php

    /**
     * Link model (not commonly used)
     */
    abstract class link_model {

        /**
         * @var array of entity data
         */
        protected $vars = [];

        /**
         * @var array of locally generated and cached data
         */
        protected $_vars = [];

        /**
         * Construct
         *
         * @param array $info entity data
         */
        public function __construct(array $info) {
            $this->vars = $info;
        }

        /**
         * Magic getter
         *
         * @param string|int|null $k key
         *
         * @return mixed
         */
        public function __get($k) {
            if (isset($this->vars[$k])) {
                return $this->vars[$k];
            }
        }

        /**
         * Update the contents of this model with new entity data
         *
         * @param array $vars new entity data
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
         * Return a new model, caching it along the way
         *
         * @param string $key cache key to be used in $this->_vars[$key] to store the model in cache
         * @param string|int|null $pk primary key of model
         * @param string $model name of model to load
         * @param mixed $default
         *
         * @return record_model|link_model|mixed
         */
        protected function _model($key, $pk, $model, $default=null) {
            if (! array_key_exists($key, $this->_vars)) {
                if ($pk !== null) {
                    $this->_vars[$key] = new $model($pk);
                } else {
                    $this->_vars[$key] = $default;
                }
            }
            return $this->_vars[$key];
        }

        /**
         * Get an associative array of entity data currently stored in this model
         *
         * @param array|null $fields
         *
         * @return array
         */
        public function export(array $fields=null) {
            if ($fields !== null && count($fields)) {
                return array_intersect_key($this->vars, array_flip($fields));
            } else {
                return $this->vars;
            }
        }

        /**
         * Sleep
         *
         * @return array
         */
        public function __sleep() {
            return [
                'vars',
            ];
        }
    }

