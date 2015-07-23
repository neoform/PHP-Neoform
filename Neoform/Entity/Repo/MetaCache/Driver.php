<?php

    namespace Neoform\Entity\Repo\MetaCache;

    use Neoform;

    /**
     * Driver interface for cache classes
     */
    interface Driver {

        /**
         * Append a value to multiple lists
         *
         * @param string[] $listKeys
         * @param string   $cacheKey
         *
         * @return int|null number of elements added
         * @throws Neoform\Entity\Repo\Exception
         */
        public function listAppend(array $listKeys, $cacheKey);

        /**
         * Append values to multiple lists
         *
         * @param string[][] $cacheKeys
         *
         * @return int|null number of elements added
         * @throws Neoform\Entity\Repo\Exception
         */
        public function listAppendMulti(array $cacheKeys);

        /**
         * Removes an item from a list
         *
         * @param string $listKey
         * @param string $cacheKey
         *
         * @return int|null
         */
        public function listRemove($listKey, $cacheKey);

        /**
         * Removes an item from multiple lists
         *
         * @param string   $cacheKey
         * @param string[] $listKeys
         *
         * @return int|null
         */
        public function listRemoveMulti($cacheKey, array $listKeys);

        /**
         * Merge multiple lists and fetch results
         *
         * @param string[] $listKeys
         *
         * @return array|null
         */
        public function listUnion(array $listKeys);

        /**
         * Get a union of multiple lists and delete the lists, all done in a single atomic operation
         *
         * @param string[] $listKeys
         *
         * @return array
         * @throws Neoform\Entity\Repo\Exception
         */
        public function listPull(array $listKeys);

        /**
         * Delete all records
         *
         * @return bool
         */
        public function flush();
    }