<?php

    abstract class entity_record_model implements arrayaccess {

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
         * @throws entity_exception
         */
        public function __construct($pk=null, array $info=null) {

            if ($pk !== null) {
                $dao = entity::dao(static::ENTITY_NAME);
                if ($this->vars = $dao->record($pk)) {
                    return;
                }
            } else if ($info !== null) {
                $this->vars = $info;
                return;
            }

            $exception = static::ENTITY_NAME . '_exception';
            throw new $exception('That ' . static::NAME . ' does not exist');
        }

        /**
         * Cannot set
         *
         * @param $k
         * @param $v
         * @throws entity_exception
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
        final public function _update(array $vars) {
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
         * this is just a shortcut for new folder_model(reset(folder_dao::by_md5($hash)));
         *
         * @param string $name
         * @param array $args
         *
         * @return entity_record_model
         */
        public static function __callstatic($name, array $args) {
            $model = static::ENTITY_NAME . '_model';
            return new $model(current(
                call_user_func_array([entity::dao(static::ENTITY_NAME), $name], $args)
            ));
        }

        /**
         * @param string        $name
         * @param integer|null  $limit
         * @param integer|null  $offset
         * @param array|null    $order_by
         *
         * @return string
         */
        final public static function _limit_var_key($name, $limit=null, $offset=null, array $order_by=null) {
            return "{$name}:{$offset}:{$limit}:" . json_encode($order_by);
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
            if ($fields) {
                return array_intersect_key($this->vars, array_flip($fields));
            } else {
                return $this->vars;
            }
        }

        /**
         * Create a model based on a field in this model
         *
         * @param string         $key        Cache name to store the model (in $this->_var[$key])
         * @param string|integer $pk         Primary key of the model
         * @param string         $model_name Name of model being loaded
         * @param mixed          $default    If model does not exist, store this value instead
         *
         * @return entity_record_model|mixed
         */
        final protected function _model($key, $pk, $model_name, $default=null) {
            if (! array_key_exists($key, $this->_vars)) {
                try {
                    if ($pk !== null) {
                        $this->_vars[$key] = new $model_name($pk);
                    } else {
                        $this->_vars[$key] = $default;
                    }
                } catch (entity_exception $e) {
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
        final public function _set_var($key, $val) {
            $this->_vars[$key] = $val;
        }

        /**
         * Attempt to set a value in this model - this is not possible
         *
         * @param string $k
         * @param mixed $v
         *
         * @throws
         */
        public function offsetSet($k, $v) {
            $exception = static::ENTITY_NAME . '_exception';
            throw new $exception('This is not an active record. Use the _update() function instead.');
        }

        /**
         * Check if a field exist in this model
         *
         * @param string $k
         *
         * @return bool
         */
        public function offsetExists($k) {
            return isset($this->vars[$k]);
        }

        /**
         * Attempt to unset a value in this model - this is not possible
         *
         * @param string $k
         *
         * @throws entity_exception
         */
        public function offsetUnset($k) {
            $exception = static::ENTITY_NAME . '_exception';
            throw new $exception('This is not an active record. You cannot unset values in this way.');
        }

        /**
         * Get a field from this model
         *
         * @param string $k
         *
         * @return mixed
         */
        public function offsetGet($k) {
            return static::__get($k);
        }
    }
