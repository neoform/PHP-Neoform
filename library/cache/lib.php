<?php

    /**
     * Caching library
     */
    class cache_lib {

        /**
         * Increment the value of a cached entry (only works if the value is an int)
         *
         * @param string  $engine
         * @param string  $key
         * @param string  $pool
         * @param integer $offset
         */
        public static function increment($engine, $key, $pool, $offset=1){

            // Memory
            cache_memory_dao::increment($key, $offset);

            if ($engine) {
                $engine = "cache_{$engine}_driver";
                $engine::increment($key, $pool, $offset);
            }
        }

        /**
         * Decrement the value of a cached entry (only works if the value is an int)
         *
         * @param string  $engine
         * @param string  $key
         * @param string  $pool
         * @param integer $offset
         */
        public static function decrement($engine, $key, $pool, $offset=1) {

            // Memory
            cache_memory_dao::decrement($key, $offset);

            if ($engine) {
                $engine = "cache_{$engine}_driver";
                $engine::decrement($key, $pool, $offset);
            }
        }

        /**
         * Checks cache for an entry, pulls from source $data_func() if not present in cache
         *
         * @param string       $engine              Which caching engines to use
         * @param string       $key                 Cache key
         * @param string       $pool                Caching pool
         * @param callable     $data_func           Source data function
         * @param mixed|null   $args                Args to pass to $data_func($args)
         * @param integer|null $ttl                 Cache length
         * @param bool         $cache_empty_results cache empty results (eg, null|false) if that is what $data_func() returns
         *
         * @return mixed returns the value from $data_func()
         */
        public static function single($engine, $key, $pool, callable $data_func, $args=null, $ttl=null, $cache_empty_results=true) {

            $engine_driver = "cache_{$engine}_driver";

            // Memory
            if (cache_memory_dao::exists($key)) {
                return cache_memory_dao::get($key);
            }

            if ($engine && $data = $engine_driver::get($key, $pool)) {

                $data = current($data);
            } else {
                //get the data from it's original source
                $data = $data_func($args);
            }

            if ($data !== null || $cache_empty_results) {

                //save to memory (always)
                cache_memory_dao::set($key, $data);

                // cache data to engine
                if ($engine) {
                    $engine_driver::set($key, $pool, $data, $ttl);
                }
            }

            return $data;
        }

        /**
         * Checks cache for an entry, pulls from source $data_func() if not present in cache
         *
         * $data_func = function(array $keys, $args) {
         *       // populate the array values..  remove any that don't exist if you want..
         *       // but any row passed back is assumed to be the correct value for that key and will be cached.
         *       return $keys;
         *   }
         *
         * $data_func must preserve the indexes in the associative array passed to it. the array merging wont work otherwise.
         *
         * @param string       $engine              Which caching engines to use
         * @param array        $rows                Rows to look up in cache
         * @param callable     $key_func            generates the cache key based on data from $rows
         * @param string       $pool                Which caching pool to use
         * @param callable     $data_func           Source data function
         * @param mixed|null   $args                args to pass to the $data_func
         * @param integer|null $ttl                 How long to cache
         * @param bool         $cache_empty_results cache empty results (eg, null|false) if that is what $data_func() returns
         *
         * @return array of mixed values from $data_func() calls
         */
        public static function multi($engine, array $rows, callable $key_func, $pool, callable $data_func, $args=null, $ttl=null, $cache_empty_results=true) {

            //this function will preserve the order of the rows

            if (! count($rows)) {
                return [];
            }

            //make a list of keys
            $key_lookup = [];
            foreach ($rows as $k => $fields) {
                $key_lookup[$k] = $key_func($fields);
            }

            $missing_rows = $key_lookup; // used for writing to cache
            $matched_rows = $key_lookup; // this results in the array keeping the exact order

            /*
             * READS
             */

            //MEMORY
            $found_in_memory = cache_memory_dao::get_multi($missing_rows);
            if (count($found_in_memory)) {
                $matched_rows = $found_in_memory + $matched_rows;
                foreach ($found_in_memory as $index => $key) {
                    unset($missing_rows[$index]);
                }
            }

            if (! count($missing_rows)) {
                return $matched_rows;
            }

            $rows_not_in_memory = $missing_rows;

            if ($engine && $missing_rows) {
                $engine = "cache_{$engine}_driver";
                foreach ($engine::get_multi($missing_rows, $pool) as $key => $row) {
                    $matched_rows[$key] = $row;
                    unset($missing_rows[$key]);
                }
            }

            //Get any missing data from the origin
            if ($missing_rows) {

                // duplicate the array, so we can know what rows need to be stored in cache
                $rows_not_in_cache = $missing_rows;

                if ($origin_rows = $data_func(array_intersect_key($rows, $missing_rows), $args)) {

                    // crunch the data sets together
                    $matched_rows = $origin_rows + $matched_rows;

                    foreach (array_keys($origin_rows) as $index) {
                        unset($missing_rows[$index]);
                    }
                }

                // still missing? doesn't exist then.. null it
                if (count($missing_rows)) {
                    foreach (array_keys($missing_rows) as $index) {
                        $matched_rows[$index] = null;
                    }
                }
            } else {
                $rows_not_in_cache = null;
            }

            // Save to memory
            if ($rows_not_in_memory) {
                $save_to_memory = [];
                foreach (array_keys($rows_not_in_memory) as $index) {
                    // either we cache empty results, or the row is not empty
                    $row = $matched_rows[$index];
                    if ($row !== null || $cache_empty_results) {
                        $save_to_memory[$key_lookup[$index]] = $row;
                    }
                }

                cache_memory_dao::set_multi($save_to_memory);
            }

            // Save to cache
            if ($rows_not_in_cache) {
                $save_to_cache = [];
                foreach (array_keys($rows_not_in_cache) as $index) {
                    // either we cache empty results, or the row is not empty
                    $row = $matched_rows[$index];
                    if ($row !== null || $cache_empty_results) {
                        $save_to_cache[$key_lookup[$index]] = $row;
                    }
                }

                $engine::set_multi($save_to_cache, $pool, $ttl);
            }

            return $matched_rows;
        }

        /**
         * Delete a cache entry
         *
         * @param string $engine
         * @param string $key
         * @param string $pool
         */
        public static function delete($engine, $key, $pool){

            // Memory
            cache_memory_dao::delete($key);

            if ($engine) {
                $engine = "cache_{$engine}_driver";
                $engine::delete($key, $pool);
            }
        }

        /**
         * Delete multiple entries from cache
         *
         * @param string $engine
         * @param array  $keys
         * @param string $pool
         */
        public static function delete_multi($engine, array $keys, $pool){

            if (count($keys)) {

                // Memory
                foreach ($keys as $key) {
                    cache_memory_dao::delete($key);
                }

                if ($engine) {
                    $engine = "cache_{$engine}_driver";
                    $engine::delete_multi($keys, $pool);
                }
            }
        }

        /**
         * Delete all keys matching a query
         * NOTE: not all cache drivers support this command (eg, memcached does not allow wildcards)
         *
         * @param string $engine
         * @param string $key
         * @param string $pool
         */
        public static function delete_wildcard($engine, $key, $pool){

            // Memory
            cache_memory_dao::delete_wildcard($key);

            if ($engine) {
                $engine = "cache_{$engine}_driver";
                $engine::delete_wildcard($key, $pool);
            }
        }

        /**
         * Delete multiple sets of keys matching a query
         * NOTE: not all cache drivers support this command (eg, memcached does not allow wildcards)
         *
         * @param string $engine
         * @param array  $keys
         * @param string $pool
         */
        public static function delete_wildcard_multi($engine, array $keys, $pool){

            // Memory
            cache_memory_dao::delete_wildcard_multi($keys);

            if ($engine) {
                $engine = "cache_{$engine}_driver";
                $engine::delete_wildcard_multi($keys, $pool);
            }
        }
    }