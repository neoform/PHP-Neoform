<?php

    namespace Neoform\Entity;

    use ArrayAccess;
    use Countable;
    use Iterator;
    use Serializable;

    abstract class Collection implements ArrayAccess, Serializable, Iterator, Countable {

        /**
         * @var Model[]
         */
        protected $models = [];

        /**
         * @var array locally cached data
         */
        protected $_vars = [];

        /**
         * Protected constructor, use the fromPks(), fromModels() or fromArrays() factories
         */
        final protected function __construct() {

        }

        /**
         * @return static
         */
        public static function fromNull() {
            return new static;
        }

        /**
         * Remove a model from the collection
         *
         * @param mixed $k Key
         *
         * @return Collection $this
         */
        public function del($k) {
            unset($this->models[$k]);

            // Reset local cache
            $this->_vars = [];

            return $this;
        }

        /**
         * Rewinds and returns the first Model
         *
         * @return Model
         */
        public function reset() {
            return reset($this->models);
        }

        /**
         * Return a slice/section of the Collection (as a new Collection)
         * 
         * @param int $limit
         * @param int $offset
         *
         * @return Collection
         */
        public function slice($limit, $offset=0) {
            $collection = new static;
            $collection->models = array_slice($this->models, $offset, $limit);
            return $collection;
        }

        /**
         * Randomizes the order of the Models in this Collection
         * 
         * @return $this
         */
        public function shuffle() {
            shuffle($this->models);
            return $this;
        }

        /**
         * Sort the collection based on $f (function) or $f field name
         *
         * @param callable|string $f
         * @param string          $order
         *
         * @return Collection
         */
        public function sort($f, $order='asc') {
            if (is_callable($f)) {
                uasort($this->models, function(Model $a, Model $b) use ($f, $order) {
                    $a = $f($a);
                    $b = $f($b);

                    if ($a === $b) {
                        return 0;
                    }

                    if ($order === 'asc') {
                        return ($a < $b) ? -1 : 1;
                    }

                    return ($a > $b) ? -1 : 1;
                });
            } else {
                uasort($this->models, function(Model $a, Model $b) use ($f, $order) {
                    $aField = $a->get($f);
                    $bField = $b->get($f);

                    if ($aField === $bField) {
                        return 0;
                    }

                    if ($order === 'asc') {
                        return ($aField < $bField) ? -1 : 1;
                    }

                    return ($aField > $bField) ? -1 : 1;
                });
            }

            return $this;
        }

        /**
         * Get the first result
         *
         * @return self
         */
        public function first() {
            return reset($this->models);
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
            if (! ($value instanceof Model)) {
                throw new \Exception('Collections only accept instances of Neoform\Entity\Model');
            }

            if ($k === null) {
                $this->models[] = $value;
            } else {
                $this->models[$k] = $value;
            }
        }

        /**
         * @param string|int $k
         *
         * @return bool
         */
        public function offsetExists($k) {
            return isset($this->models[$k]);
        }

        /**
         * @param string|int $k
         */
        public function offsetUnset($k) {
            // Reset local cache
            $this->_vars = [];
            unset($this->models[$k]);
        }

        /**
         * @param string|int $k
         *
         * @return Model|null
         */
        public function offsetGet($k) {
            return isset($this->models[$k]) ? $this->models[$k] : null;
        }

        /**
         * Serializable Interface -------------------------------------------------------------
         */

        /**
         * @return string
         */
        public function serialize() {
            return serialize($this->models);
        }

        /**
         * @param string $serializedData
         */
        public function unserialize($serializedData) {
            $this->models = @unserialize($serializedData);
        }

        /**
         * Iterator Interface -------------------------------------------------------------
         */

        /**
         * Rewind
         */
        public function rewind() {
            reset($this->models);
        }

        /**
         * Current
         *
         * @return Model
         */
        public function current() {
            return current($this->models);
        }

        /**
         * Key
         *
         * @return string|int
         */
        public function key() {
            return key($this->models);
        }

        /**
         * Next
         *
         * @return Model
         */
        public function next() {
            next($this->models);
        }

        /**
         * Valid
         *
         * @return bool
         */
        public function valid() {
            return key($this->models) !== null;
        }

        /**
         * Countable Interface -------------------------------------------------------------
         */

        /**
         * @return int
         */
        public function count() {
            return count($this->models);
        }
    }