<?php

    abstract class record_model {

        /**
         * @var array of values representing the entity
         */
        protected $vars;

        /**
         * @var array of values generated and cached (calculated fields)
         */
        protected $_vars = [];

        /**
         * @param string|integer|null  $pk   Primary key of the entity
         * @param array                $info Manually load model with entity data
         * @throws record_exception
         */
        public function __construct($pk=null, array $info=null) {

            if ($pk !== null) {
                $dao = static::ENTITY_NAME . '_dao';
                if ($this->vars = $dao::by_pk($pk)) {
                    return;
                }
            } else if ($info !== null) {
                $this->vars = $info;
                return;
            }

            $exception = "{static::ENTITY_NAME}_exception";
            throw new $exception('That ' . static::NAME . ' does not exist');
        }

        /**
         * Cannot set
         *
         * @param $k
         * @param $v
         * @throws model_exception
         */
        final public function __set($k, $v) {
            $exception = static::ENTITY_NAME . '_exception';
            throw new $exception('This is not an active record. Use the _update() function instead.');
        }

        /**
         * Update the current model with new data
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
         * Empty the current model
         */
        public function reset() {
            $this->_vars = [];
            $this->vars  = [];
        }

        /**
         * Reload the current model with from its source
         */
        public function reload() {
            $this->_vars = [];
            $dao         = static::ENTITY_NAME . '_dao';
            $pk_name     = $dao::PRIMARY_KEY;
            $this->__construct($this->$pk_name);
        }

        /**
         * Get a model by a given field or fields
         * folder_model::by_md5($hash) will return a folder model.
         * this is just a shortcut for new folder_model(current(folder_dao::by_md5($hash)));
         *
         * @param string $name
         * @param array $args
         *
         * @return record_model
         */
        public static function __callstatic($name, $args) {
            $model = static::ENTITY_NAME . '_model';
            $dao   = static::ENTITY_NAME . '_dao';
            return new $model(current(
                call_user_func_array("$dao::$name", $args)
            ));
        }

        /**
         * var_export() and print_r() use this internally
         *
         * @return array
         */
        public function __sleep() {
            return [
                'vars',
            ];
        }

        /**
         * Exports data from the model
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
         * @param string         $key        Cache name to store the model (in $this->_var[$key])
         * @param string|integer $pk         Primary key of the model
         * @param string         $model_name Name of model being loaded
         * @param mixed          $default    If model does not exist, store this value instead
         *
         * @return record_model|mixed
         */
        protected function _model($key, $pk, $model_name, $default=null) {
            if (! array_key_exists($key, $this->_vars)) {
                try {
                    if ($pk !== null) {
                        $this->_vars[$key] = new $model_name($pk);
                    } else {
                        $this->_vars[$key] = $default;
                    }
                } catch (model_exception $e) {
                    $this->_vars[$key] = $default;
                }
            }
            return $this->_vars[$key];
        }

        /**
         * This allows the preloading of $this->_vars values
         * @param string $key
         * @param mixed  $val
         */
        public function _set_var($key, $val) {
            $this->_vars[$key] = $val;
        }
    }
