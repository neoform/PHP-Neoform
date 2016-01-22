<?php

    namespace Neoform\Input;

    use ArrayAccess;
    use Countable;
    use Iterator;
    use Serializable;

    class Collection implements Input, ArrayAccess, Serializable, Iterator, Countable {

        /**
         * @var Input[]
         */
        protected $inputs = [];

        /**
         * @var array
         */
        protected $data = [];

        /**
         * Only to be used if an array was supplied when a non-array was expected
         *
         * @var string
         */
        protected $error;

        /**
         * @param array $arr
         */
        public function __construct(array $arr) {
            foreach ($arr as $k => $v) {
                if (is_array($v)) {
                    $this->inputs[$k] = new Collection($v);
                } else {
                    $this->inputs[$k] = new Model($v);
                }
            }
        }

        /**
         * @param array $arr
         *
         * @return static
         */
        public static function fromArray(array $arr) {
            return new static($arr);
        }

        /**
         * Block setting values this way
         *
         * @param string $k
         * @param mixed $v
         */
        public function __set($k, $v) {
            //$this[$k] = $v;
        }

        /**
         * Get an input model
         * 
         * @param string $k
         *
         * @return Model
         */
        public function __get($k) {
            if (! isset($this->inputs[$k])) {
                $this->inputs[$k] = new Model(null, false);
            }

            return $this->inputs[$k];
        }

        /**
         * @param string $type
         * @param bool   $strict
         *
         * @return $this
         */
        public function requireTypeCast($type, $strict=false) {
            if ($type !== 'array') {
                $this->error = 'Invalid type';
            }
            return $this;
        }

        /**
         * Dummy function
         *
         * @param $val
         *
         * @return $this
         */
        public function setVal($val) {
            $this->error = 'Invalid type';
            return $this;
        }

        /**
         * Dummy function
         *
         * @return $this
         */
        public function unSetVal() {
            $this->error = 'Invalid type';
            return $this;
        }

        /**
         * Dummy function
         *
         * @return $this
         */
        public function unSetValIfEmpty() {
            $this->error = 'Invalid type';
            return $this;
        }
        
        /**
         * Check if an input model exists
         * 
         * @param string $k key
         * 
         * @return boolean
         */
        public function inputExists($k) {
            return isset($this->inputs[$k]) && $this->inputs[$k]->exists();
        }

        /**
         * Dummy function
         *
         * @return boolean
         */
        public function exists() {
            $this->error = 'Invalid type';
            return false;
        }

        /**
         * @return array
         */
        public function getVal() {
            $args = func_get_args();
            if ($count = count($args)) {
                if ($count === 1) {
                    if (! is_array($args[0])) {
                        return isset($this->inputs[$args[0]]) ? $this->inputs[$args[0]]->getVal() : null;
                    }
                }

                $inputs = array_intersect_key($this->inputs, array_flip($args));
            } else {
                $inputs = $this->inputs;
            }

            $vals = [];
            foreach ($inputs as $k => $input) {
                $vals[$k] = $input->getVal();
            }

            return $vals;
        }

        /**
         * @param array $keys
         * @param bool  $includeEmptyFields
         *
         * @return array
         */
        public function getVals(array $keys, $includeEmptyFields=true) {
            $vals = [];
            foreach ($keys as $key) {

                // Must exist
                if (! isset($this->inputs[$key])) {
                    continue;
                }

                $input = $this->inputs[$key];

                // Must have been created properly
                if (! $input->exists()) {
                    continue;
                }

                // Must have been validated
                if (! $input->isValidated()) {
                    continue;
                }

                // If not $includeEmptyFields and the value is empty and optional
                if (! $includeEmptyFields && $input->isEmpty() && $input->isOptional()) {
                    continue;
                }

                $vals[$key] = $input->isEmpty() ? $input->getDefaultVal() : $input->getVal();
            }

            return $vals;
        }

        /**
         * Dummy function
         *
         * @return null
         */
        public function getDefaultVal() {
            $this->error = 'Invalid type';
            return null;
        }

        /**
         * @param array $keys
         * @param bool  $emptyOptionalFields
         *
         * @return array
         */
        public function toArray(array $keys, $emptyOptionalFields=true) {
            $vals = [];
            foreach ($keys as $key) {
                if (isset($this->inputs[$key]) && $this->inputs[$key]->exists()) {
                    if ($emptyOptionalFields || ! $this->inputs[$key]->isEmpty()) {
                        $vals[$key] = $this->inputs[$key]->getVal();
                    }
                }
            }

            return $vals;
        }

        /**
         * This function being called is considered an error (happens when a non-existent function is called)
         * this happens when a collection is being used as if it were a model…  (because an array was passed in place
         * a string or int.. etc)
         *
         * @param string $name
         * @param array $args
         *
         * @return $this
         */
        public function __call($name, array $args) {
            $this->error = 'Invalid type';
            return $this;
        }

        /**
         * Get one or more entries (Model, not value)
         *
         * @return array|Collection
         */
        public function get() {
            if ($args = func_get_args()) {
                if (count($args) === 1) {
                    if (! is_array($args[0])) {
                        if (! isset($this->inputs[$args[0]])) {
                            $this->inputs[$args[0]] = new Model(null, false);
                        }

                        return $this->inputs[$args[0]];
                    }
                }

                return array_intersect_key($this->inputs, array_flip($args));

            } else {
                return $this;
            }
        }

        /**
         * @param Validator $validator
         *
         * @return $this
         */
        public function applyValidation(Validator $validator) {
            $validator->validate($this);
            return $this;
        }

        /**
         * @param string      $k
         * @param string|null $typeCast
         * @param bool        $isOptional
         * @param mixed|null  $defaultValue
         *
         * @return Model
         */
        public function validate($k, $typeCast=null, $isOptional=false, $defaultValue=null) {
            if (! isset($this->inputs[$k])) {
                $this->inputs[$k] = new Model(null, false);
            }

            // Mark as optional prior to casting
            if ($isOptional) {
                // Only optional values make use of default values
                $this->inputs[$k]->markAsOptional($defaultValue);
            }

            // Cast the value's type
            if ($typeCast) {
                $this->inputs[$k]->requireTypeCast($typeCast);
            }

            // Mark as validated and return
            return $this->inputs[$k]->markAsValidated();
        }

        /**
         * Has all the input collection's entries been validated
         *
         * @return bool
         */
        public function isValidated() {
            if (! count($this->inputs)) {
                return false;
            }

            foreach ($this->inputs as $input) {
                if (! $input->isValidated()) {
                    return false;
                }
            }
            return true;
        }

        /**
         * Have specific entries been validated
         *
         * @param string[] $entryNames
         *
         * @return bool
         */
        public function isValidatedEntries(array $entryNames) {
            foreach ($entryNames as $entryName) {
                if (! isset($this->inputs[$entryName]) || ! $this->inputs[$entryName]->isValidated()) {
                    return false;
                }
            }
            return true;
        }

        /**
         * @return bool
         */
        public function isValid() {
            if ($this->error) {
                return false;
            }
            foreach ($this->inputs as $input) {
                if (! $input->isValid()) {
                    return false;
                }
            }
            return true;
        }

        /**
         * @return bool
         */
        public function isOptional() {
            $this->error = 'Invalid type';
            return false;
        }

        /**
         * @return bool
         */
        public function isEmpty() {
            return ! count($this->inputs);
        }

        /**
         * Is this a collection
         *
         * @return bool
         */
        public function isCollection() {
            return true;
        }

        /**
         * Dummy function
         *
         * @return $this
         */
        public function markAsValidated() {
            $this->error = 'Invalid type';
            return $this;
        }

        /**
         * @param string|int $k
         * @param mixed      $v
         *
         * @return $this
         */
        public function setData($k, $v) {
            $this->data[$k] = $v;
            return $this;
        }

        /**
         * @param string|int $k
         *
         * @return mixed|null
         */
        public function getData($k) {
            if (isset($this->data[$k])) {
                return $this->data[$k];
            }
        }

        /**
         * @return $this
         */
        public function resetErrors() {
            $this->error = null;
            return $this;
        }

        /**
         * @return Error\Collection
         */
        public function getErrors() {
            if ($this->error === null) {
                $errors = new Error\Collection;
            } else {
                $errors = new Error\Collection([
                    $this->error,
                ]);
            }

            foreach ($this->inputs as $k => $input) {
                $err = $input->getErrors();
                if ($err !== null) {
                    if (! $err instanceof Error\Collection || $err->count()) {
                        $errors->$k = $err;
                    }
                }
            }
            return $errors;
        }

        /**
         * @param string $set
         *
         * @return $this
         */
        public function setErrors($set) {
            $this->error = $set;
            return $this;
        }

        /**
         * @return Exception
         */
        public function getException() {
            return new Exception($this->getErrors());
        }

        /**
         * Transfer any errors from one input to another
         *
         * @param Input $input
         *
         * @return $this
         */
        public function transferErrorsTo(Input $input) {
            if ($this->getErrors() && ! $input->getErrors()) {
                $input->setErrors($this->getErrors());
                $this->resetErrors();
            }
            return $this;
        }

        /**
         * @param int|null $min
         * @param int|null $max
         *
         * @return $this
         */
        public function requireCount($min, $max) {
            if ($min || $max) {
                $count = count($this->inputs);
                if ($min === $max && $count !== $min) {
                    $this->error = "{$min} required";
                } else if ($min && $count < $min) {
                    $this->error = "{$min} minimum";
                } else if ($max && $count > $max) {
                    $this->error = "{$min} maximum";
                }
            }
            return $this;
        }

        /**
         * @param callable $func
         *
         * @return $this
         */
        public function each(callable $func) {
            foreach ($this->inputs as $k => $input) {
                $func($input, $k, $this);
            }
            return $this;
        }

        /**
         * Get rid of duplicates
         *
         * @return $this
         */
        public function forceUnique() {
            if (count($this->inputs)) {
                $keys   = [];
                $remove = [];
                foreach ($this->inputs as $k => $input) {
                    if (isset($keys[$input->getVal()])) {
                        $remove[] = $k;
                    } else {
                        $keys[$input->getVal()] = true;
                    }
                }

                if ($remove) {
                    foreach ($remove as $k) {
                        unset($this[$k]);
                    }
                }
            }

            return $this;
        }

        /**
         * This is a collection, not the model
         *
         * @param callable $func
         *
         * @return $this
         */
        public function callback(callable $func) {
            $this->error = 'invalid type (array given)';
            return $this;
        }

        /**
         * @param mixed|null $defaultValue
         *
         * @return $this
         */
        public function markAsOptional($defaultValue=null) {
            //empty function
            return $this;
        }

        /**
         * ArrayAccess Interface -------------------------------------------------------------
         */

        /**
         * @param string|int $k
         * @param Model $value
         *
         * @throws \Exception
         */
        public function offsetSet($k, $value) {
            if (! ($value instanceof Input)) {
                throw new \Exception('Input collections only accept instances of Neoform\Input\Input');
            }

            if ($k === null) {
                $this->inputs[] = $value;
            } else {
                $this->inputs[$k] = $value;
            }
        }

        /**
         * @param string|int $k
         *
         * @return bool
         */
        public function offsetExists($k) {
            return isset($this->inputs[$k]);
        }

        /**
         * @param string|int $k
         */
        public function offsetUnset($k) {
            // Reset local cache
            $this->_vars = [];
            unset($this->inputs[$k]);
        }

        /**
         * @param string|int $k
         *
         * @return Model|null
         */
        public function offsetGet($k) {
            if (! isset($this->inputs[$k])) {
                $this->inputs[$k] = new Model(null, false);
            }

            return $this->inputs[$k];
        }

        /**
         * Serializable Interface -------------------------------------------------------------
         */

        /**
         * @return string
         */
        public function serialize() {
            return serialize($this->inputs);
        }

        /**
         * @param string $serializedData
         */
        public function unserialize($serializedData) {
            $this->inputs = @unserialize($serializedData);
        }

        /**
         * Iterator Interface -------------------------------------------------------------
         */

        /**
         * Rewind
         */
        public function rewind() {
            reset($this->inputs);
        }

        /**
         * Current
         *
         * @return Model
         */
        public function current() {
            return current($this->inputs);
        }

        /**
         * Key
         *
         * @return string|int
         */
        public function key() {
            return key($this->inputs);
        }

        /**
         * Next
         *
         * @return Model
         */
        public function next() {
            next($this->inputs);
        }

        /**
         * Valid
         *
         * @return bool
         */
        public function valid() {
            return key($this->inputs) !== null;
        }

        /**
         * Countable Interface -------------------------------------------------------------
         */

        /**
         * @return int
         */
        public function count() {
            return count($this->inputs);
        }
    }
