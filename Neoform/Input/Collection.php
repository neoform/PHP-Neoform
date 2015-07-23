<?php

    namespace Neoform\Input;

    class Collection extends \arrayobject {

        protected $data = [];
        protected $error; //only to be used if an array was supplied when a non-array was expected

        /**
         * @param array $arr
         */
        public function __construct(array $arr) {
            foreach ($arr as $k => $v) {
                if (is_array($v)) {
                    $this[$k] = new Collection($v);
                } else {
                    $this[$k] = new Model($v);
                }
            }
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
         * @return mixed
         */
        public function __get($k) {
            if (! isset($this[$k])) {
                $this[$k] = new Model(null, false);
            }

            return $this[$k];
        }
        
        /**
         * Check if an input model exists
         * 
         * @param string $k key
         * 
         * @return boolean
         */
        public function exists($k) {
            return isset($this[$k]) && $this[$k]->exists(); 
        }

        /**
         * This function being called is considered an error (happens when a non-existent function is called)
         * this happens when a collection is being used as if it were a model…  (because an array was passed in place
         * a string or int.. etc)
         *
         * @param string $name
         * @param array $args
         *
         * @return collection
         */
        public function __call($name, array $args) {
            $this->error = 'Invalid type';
            return $this;
        }

        /**
         * Get one or more entries (Model, not value)
         *
         * @return array|collection
         */
        public function get() {
            if ($args = func_get_args()) {
                if (count($args) === 1) {
                    if (! is_array($args[0])) {
                        if (! isset($this[$args[0]])) {
                            $this[$args[0]] = new Model(null, false);
                        }

                        return $this[$args[0]];
                    }
                }

                return array_intersect_key($this, array_flip($args));

            } else {
                return $this;
            }
        }

        /**
         * @return array|null
         */
        public function val() {
            $args = func_get_args();
            if ($count = count($args)) {
                if ($count === 1) {
                    if (! is_array($args[0])) {
                        return isset($this[$args[0]]) ? $this[$args[0]]->val() : null;
                    }
                }

                $entries = array_intersect_key((array) $this, array_flip($args));
            } else {
                $entries = (array) $this;
            }

            $vals = [];
            foreach ($entries as $k => $entry) {
                $vals[$k] = $entry->val();
            }
            return $vals;
        }

        /**
         * @param array $keys
         * @param bool  $empty_optional_fields
         *
         * @return array
         */
        public function vals(array $keys, $empty_optional_fields=true) {
            $vals = [];
            foreach ($keys as $key) {
                if (isset($this[$key])) {
                    if ($empty_optional_fields || ! $this[$key]->is_empty()) {
                        $vals[$key] = $this[$key]->val();
                    }
                }
            }
            return $vals;
        }

        /**
         * @param mixed|null $k
         * @param mixed|null $v
         *
         * @return collection
         */
        public function data($k=null, $v=null) {
            if ($v !== null) {
                $this->data[$k] = $v;
                return $this;
            } else if (isset($this->data[$k])) {
                return $this->data[$k];
            }
        }

        /**
         * @return bool
         */
        public function is_valid() {
            if ($this->error) {
                return false;
            }
            foreach ($this as $entry) {
                if (! $entry->is_valid()) {
                    return false;
                }
            }
            return true;
        }

        /**
         * @return collection
         */
        public function reset_errors() {
            $this->error = null;
            return $this;
        }

        /**
         * @param null $set
         *
         * @return collection|Error\Collection
         */
        public function errors($set=null) {
            if ($set) {
                $this->error = $set;
                return $this;
            } else {
                if ($this->error === null) {
                    $errors = new Error\Collection;
                } else {
                    $errors = new Error\Collection([
                        $this->error,
                    ]);
                }

                foreach ($this as $k => $entry) {
                    $err = $entry->errors();
                    if ($err !== null) {
                        if (! $err instanceof Error\Collection || $err->count()) {
                            $errors->$k = $err;
                        }
                    }
                }
                return $errors;
            }
        }

        /**
         * @return exception
         */
        public function exception() {
            return new Exception($this->errors());
        }
        /**
         * @param null $min
         * @param null $max
         *
         * @return int|collection
         */
        public function count($min=null, $max=null) {
            $count = count($this);
            if ($min || $max) {
                if ($min === $max && $count !== $min) {
                    $this->error = "{$min} required";
                } else if ($min && $count < $min) {
                    $this->error = "{$min} minimum";
                } else if ($max && $count > $max) {
                    $this->error = "{$min} maximum";
                }
                return $this;
            } else {
                return $count;
            }
        }

        /**
         * @param callable $func
         *
         * @return collection
         */
        public function each($func) {
            foreach ($this as $k => $entry) {
                $func($entry, $k, $this);
            }
            return $this;
        }

        /**
         * Get rid of duplicates
         *
         * @return collection
         */
        public function unique() {
            if (count($this)) {
                $keys   = [];
                $remove = [];
                foreach ($this as $k => $entry) {
                    if (isset($keys[$entry->val()])) {
                        $remove[] = $k;
                    } else {
                        $keys[$entry->val()] = true;
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
         * @return collection
         */
        public function callback(callable $func) {
            $this->error = 'invalid type (array given)';
            return $this;
        }

        /**
         * @return collection
         */
        public function optional() {
            //empty function
            return $this;
        }
    }
