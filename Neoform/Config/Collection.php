<?php

    namespace Neoform\Config;

    use Iterator;

    class Collection implements Iterator {

        /**
         * @var Model[]
         */
        protected $models = [];

        /**
         * Add a config to the collection
         *
         * @param Model $model
         */
        public function add(Model $model) {
            $this->models[get_class($model)] = $model;
        }

        /**
         * @param string $k
         *
         * @return Builder
         * @throws Exception
         */
        public function get($k) {
            if (! isset($this->models[$k])) {
                throw new Exception("Config \"{$k}\" does not exist");
            }

            return $this->models[$k];
        }

        /**
         * @param string $k
         *
         * @return bool
         */
        public function exists($k) {
            return isset($this->models[$k]);
        }

        /**
         * reset()
         */
        public function rewind() {
            reset($this->models);
        }

        /**
         * @return Model
         */
        public function current() {
            return current($this->models);
        }

        /**
         * @return string
         */
        public function key() {
            return key($this->models);
        }

        /**
         * @return Model
         */
        public function next() {
            return next($this->models);
        }

        /**
         * @return bool
         */
        public function valid() {
            $key = key($this->models);
            return ($key !== null && $key !== false);
        }
    }