<?php

    namespace Neoform\Entity\Repo;

    /**
     * Do not abort a script, even if the user stops loading the page
     * If a cache engine query terminates in the middle of cache deletion, that can cause damaged/dirty cache.
     */
    ignore_user_abort(1);

    /**
     * Cache
     *
     * This class' functions are somewhat complex due to what they do, and are designed for performance more than anything
     *
     * @package Neoform\Cache
     */
    class Cache {

        /**
         * @var Cache\Driver
         */
        protected $readRepo;

        /**
         * @var Cache\Driver
         */
        protected $writeRepo;

        /**
         * Construct
         */
        public function __construct(Cache\Driver $readRepo, Cache\Driver $writeRepo) {
            $this->readRepo  = $readRepo;
            $this->writeRepo = $writeRepo;
        }

        /**
         * Checks cache for an entry, pulls from source $dataFunc() if not present in cache
         *
         * @param string   $key                 Cache key
         * @param callable $dataFunc            Source data function
         * @param callable $afterCacheFunc      After cache function
         * @param bool     $cacheEmptyResults   cache empty results (eg, null|false) if that is what $dataFunc() returns
         *
         * @return mixed returns the value from $dataFunc()
         */
        public function single($key, callable $dataFunc, callable $afterCacheFunc=null, $cacheEmptyResults=false) {

            if ($data = $this->readRepo->get($key)) {
                return reset($data);
            }

            // Not found in cache - get the data from it's original source
            $data = $dataFunc();

            if ($data !== null || $cacheEmptyResults) {

                // cache data to engine
                $this->writeRepo->set($key, $data);

                if ($afterCacheFunc) {
                    $afterCacheFunc($key, $data);
                }
            }

            return $data;
        }

        /**
         * Checks cache for an entry, pulls from source $dataFunc() if not present in cache
         *
         * $dataFunc = function(array $keys) {
         *       // populate the array values..  remove any that don't exist if you want..
         *       // but any row passed back is assumed to be the correct value for that key and will be cached.
         *       return $keys;
         *   }
         *
         * $dataFunc must preserve the indexes in the associative array passed to it. the array merging wont work otherwise.
         *
         * @param array        $rows              Rows to look up in cache
         * @param callable     $keyFunc           generates the cache key based on data from $rows
         * @param callable     $dataFunc          Source data function(array $keys)
         * @param callable     $afterCacheFunc    After cache function(array $cacheKeys, array $fieldValsArr, array $pkResultsArr)
         * @param bool         $cacheEmptyResults cache empty results (eg, null|false) if that is what $dataFunc() returns
         *
         * @return array of mixed values from $dataFunc() calls
         */
        public function multi(array $rows, callable $keyFunc, callable $dataFunc, callable $afterCacheFunc=null, $cacheEmptyResults=false) {
            if (! $rows) {
                return [];
            }

            //make a list of keys
            $keyLookup = [];
            foreach ($rows as $k => $fields) {
                /**
                 * function(array $fieldValsArr) { ... }
                 */
                $keyLookup[$k] = $keyFunc($fields);
            }

            $missingRows = $keyLookup; // used for writing to cache
            $matchedRows = $keyLookup; // this results in the array keeping the exact order

            /**
             * Cache - Read
             */
            if ($missingRows) {
                foreach ($this->readRepo->getMulti($missingRows) as $key => $row) {
                    $matchedRows[$key] = $row;
                    unset($missingRows[$key]);
                }
            }

            /**
             * Source - Read
             */
            if ($missingRows) {

                /**
                 * Duplicate the array, so we can know what rows need to be stored in cache
                 */
                $rowsNotInCache = $missingRows;

                /**
                 * function(array $fieldValsArr) { ... }
                 */
                if ($originRows = $dataFunc(array_intersect_key($rows, $missingRows))) {
                    foreach ($originRows as $key => $val) {
                        $matchedRows[$key] = $val;
                        unset($missingRows[$key]);
                    }
                }

                /**
                 * Record still missing? Doesn't exist anywhere then.. set the value to null
                 */
                if ($missingRows) {
                    foreach (array_keys($missingRows) as $index) {
                        $matchedRows[$index] = null;
                    }
                }
            } else {
                $rowsNotInCache = null;
            }

            /**
             * Cache - Write
             */
            if ($rowsNotInCache) {
                $saveToCache = [];
                foreach (array_intersect_key($matchedRows, $rowsNotInCache) as $index => $row) {
                    /**
                     * Either we cache empty results, or the row is not empty
                     */
                    if ($row !== null || $cacheEmptyResults) {
                        $saveToCache[$keyLookup[$index]] = $row;
                    }
                }

                $this->writeRepo->setMulti($saveToCache);
            }

            if ($rowsNotInCache && $afterCacheFunc) {
                /**
                 * We only pass the rows that were newly cached, not all rows being returned
                 * (eg, not including those already in cache)
                 *
                 * function(array $cacheKeys, array $fieldValsArr, array $results) { ... }
                 */
                $afterCacheFunc(
                    $rowsNotInCache,                                   // $cacheKeys    - cache keys used
                    array_intersect_key($rows, $rowsNotInCache),       // $fieldValsArr - the field vals
                    array_intersect_key($matchedRows, $rowsNotInCache) // $results - pks, counts, whatever came from source
                );
            }

            return $matchedRows;
        }
    }