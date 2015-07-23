<?php

    namespace Neoform\Entity\Repo\MetaCache\Driver;

    use Neoform;

    class None implements Neoform\Entity\Repo\MetaCache\Driver {

        /**
         * @var None
         */
        protected static $instance;

        /**
         * @param string $enginePool
         *
         * @return None|static
         */
        public static function getInstance($enginePool) {
            if (! self::$instance) {
                self::$instance = new static;
            }

            return self::$instance;
        }

        /**
         * Append a value to multiple lists
         *
         * @param string[] $listKeys
         * @param string   $cacheKey to be put in the lists
         *
         * @return int number of elements added
         * @throws Neoform\Entity\Repo\Exception
         */
        public function listAppend(array $listKeys, $cacheKey) {
            return 0;
        }

        /**
         * Append values to a list
         *
         * @param string[][] $cacheKeys
         *
         * @return int number of elements added
         * @throws Neoform\Entity\Repo\Exception
         */
        public function listAppendMulti(array $cacheKeys) {
            return 0;
        }

        /**
         * Removes an item from a list
         *
         * @param string $listKey
         * @param string $cacheKey
         *
         * @return int
         */
        public function listRemove($listKey, $cacheKey) {
            return 0;
        }

        /**
         * Removes an item from multiple lists
         *
         * @param string   $cacheKey
         * @param string[] $listKeys
         *
         * @return int
         */
        public function listRemoveMulti($cacheKey, array $listKeys) {
            return 0;
        }

        /**
         * Merge multiple lists and fetch results
         *
         * @param string[] $listKeys
         *
         * @return array
         */
        public function listUnion(array $listKeys) {
            return [];
        }

        /**
         * Get a union of multiple lists
         *
         * @param string[] $listKeys
         *
         * @return array
         * @throws Neoform\Entity\Repo\Exception
         */
        public function listPull(array $listKeys) {
            return [];
        }

        /**
         * @return bool
         */
        public function flush() {
            return true;
        }
    }