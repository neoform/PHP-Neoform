<?php

    namespace Neoform\Config\Builder;

    use Iterator;
    use Neoform\Config\Builder;
    use Neoform\Config\Exception;
    use Neoform;

    class Collection implements Iterator {

        /**
         * @var Builder[]
         */
        protected $modelBuilders = [];

        /**
         * @return Neoform\Config\Collection
         */
        public function build() {
            $collection = new Neoform\Config\Collection;
            foreach ($this->modelBuilders as $modelBuilder) {
                $collection->add($modelBuilder->build());
            }
            return $collection;
        }

        /**
         * Add a config builder to the collection
         *
         * @param Builder $builder
         */
        public function add(Builder $builder) {
            $this->modelBuilders[$builder->getKey()] = $builder;
        }

        /**
         * @param string $k
         *
         * @return Builder
         * @throws Exception
         */
        public function get($k) {
            if (! isset($this->modelBuilders[$k])) {
                throw new Exception("Post validation check of config failed. Config \"{$k}\" does not exist");
            }

            return $this->modelBuilders[$k];
        }

        /**
         * @param string $k
         *
         * @return bool
         */
        public function exists($k) {
            return isset($this->modelBuilders[$k]);
        }

        /**
         * reset()
         */
        public function rewind() {
            reset($this->modelBuilders);
        }

        /**
         * @return Builder
         */
        public function current() {
            return current($this->modelBuilders);
        }

        /**
         * @return string
         */
        public function key() {
            return key($this->modelBuilders);
        }

        /**
         * @return Builder
         */
        public function next() {
            return next($this->modelBuilders);
        }

        /**
         * @return bool
         */
        public function valid() {
            $key = key($this->modelBuilders);
            return $key !== null && $key !== false;
        }
    }