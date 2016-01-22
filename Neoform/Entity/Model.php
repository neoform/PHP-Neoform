<?php

    namespace Neoform\Entity;

    use ArrayAccess;

    abstract class Model implements ArrayAccess, Entity {

        /**
         * @var array of values representing the entity
         */
        protected $vars;

        /**
         * @var array of values generated and cached (calculated fields)
         */
        protected $_vars = [];

        /**
         * @param string|int $k
         *
         * @return mixed
         */
        abstract public function get($k);

        /**
         * Protected constructor, use the fromPk() or fromArray() factories
         */
        final protected function __construct() {

        }

        /**
         * Generate a model from an array of info
         *
         * @param array $vars
         *
         * @return static
         */
        public static function fromArray(array $vars) {
            $self = new static;
            $self->vars = $vars;
            return $self;
        }

        /**
         * Get an associative array of entity data currently stored in this model
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
         * Get model details as an associative array
         *
         * @return array
         */
        public function toArray() {
            return $this->vars;
        }

        /**
         * Cannot set
         *
         * @param $k
         * @param $v
         * @throws exception
         */
        final public function __set($k, $v) {
            $exception = '\\' . static::getNamespace() . '\\Exception';
            throw new $exception('This is not an active record. Use the _update() function instead.');
        }

        /**
         * Sleep
         *
         * @return array
         */
        public function __sleep() {
            return [ 'vars', ];
        }

        /**
         * Update the current model with new data
         *
         * @param array $vars
         */
        final public function _update(array $vars) {
            // Clean the temp vars array
            $this->_vars = [];

            // Apply the new vars
            foreach ($vars as $k => $v) {
                $this->vars[$k] = $v;
            }
        }

        /**
         * Empty the current model
         *
         * @return $this
         */
        public function reset() {
            $this->_vars = [];
            $this->vars  = [];
            return $this;
        }

        /**
         * Create a model based on a field in this model
         *
         * @param string         $key       Cache name to store the model (in $this->_var[$key])
         * @param string|integer $pk        Primary key of the model
         * @param string         $modelName Name of model being loaded
         * @param mixed          $default   If model does not exist, store this value instead
         *
         * @return Model|mixed
         */
        final protected function _model($key, $pk, $modelName, $default=null) {
            if (! array_key_exists($key, $this->_vars)) {
                try {
                    if ($pk !== null) {
                        $modelName = "\\{$modelName}";

                        if (is_subclass_of($modelName, 'Neoform\Entity\Record\Model')) {
                            $this->_vars[$key] = $modelName::fromPk($pk);
                        } else {
                            $this->_vars[$key] = new $modelName($pk);
                        }
                    } else {
                        $this->_vars[$key] = $default;
                    }
                } catch (Exception $e) {
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
        final public function _preloadCache($key, $val) {
            $this->_vars[$key] = $val;
        }

        /**
         * @param string        $name
         * @param integer|null  $limit
         * @param integer|null  $offset
         * @param array|null    $orderBy
         *
         * @return string
         */
        final public static function _limitVarKey($name, array $orderBy=null, $limit=null, $offset=null) {
            if ($orderBy) {
                ksort($orderBy);
                return "{$name}:{$offset}:{$limit}:" . json_encode($orderBy); // @todo what if there are binary values...? :(
            } else {
                return $name;
            }
        }

        /**
         * @param string $name
         * @param array  $fieldvals
         *
         * @return string
         */
        final public static function _countVarKey($name, array $fieldvals=null) {
            if ($fieldvals) {
                ksort($fieldvals);
                return "{$name}:" . json_encode($fieldvals); // @todo what if there are binary values...? :(
            } else {
                return $name;
            }
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
            $exception = '\\' . static::getNamespace() . '\\Exception';
            throw new $exception('This is an immutable object, you cannot change its properties');
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
         * @throws exception
         */
        public function offsetUnset($k) {
            $exception = '\\' . static::getNamespace() . '\\Exception';
            throw new $exception('This is an immutable object, you cannot change its properties');
        }

        /**
         * Get a field from this model
         *
         * @param string $k
         *
         * @return mixed
         */
        public function offsetGet($k) {
            return static::get($k);
        }
    }