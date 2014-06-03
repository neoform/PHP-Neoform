<?php

    namespace neoform\cache;

    /**
     * Do not abort a script, even if the user stops loading the page
     * If a cache engine query terminates in the middle of cache deletion, that can cause damaged/dirty cache.
     */
    ignore_user_abort(1);

    /**
     * Caching library
     */
    class lib {

        /**
         * Activate a pipelined (batch) query
         *
         * @param string $engine
         * @param string $engine_pool
         */
        public static function pipeline_start($engine, $engine_pool) {
            if ($engine) {
                $engine_driver = "\\neoform\\cache\\driver\\{$engine}";
                $engine_driver::pipeline_start($engine_pool);
            }
        }

        /**
         * Execute pipelined (batch) queries and return result
         *
         * @param string $engine
         * @param string $engine_pool
         *
         * @return mixed result of batch operation
         */
        public static function pipeline_execute($engine, $engine_pool) {
            if ($engine) {
                $engine_driver = "\\neoform\\cache\\driver\\{$engine}";
                return $engine_driver::pipeline_execute($engine_pool);
            }
        }

        /**
         * Set a record in cache
         *
         * @param string  $engine
         * @param string  $engine_pool
         * @param bool    $cache_engine_memory Use memory cache
         * @param string  $key
         * @param mixed   $data
         * @param integer $ttl seconds
         *
         * @return mixed|null
         */
        public static function set($engine, $engine_pool, $cache_engine_memory, $key, $data, $ttl=null) {

            // Memory
            if ($cache_engine_memory) {
                driver\memory::set(null, $key, $data);
            }

            if ($engine) {
                $engine_driver = "\\neoform\\cache\\driver\\{$engine}";
                $engine_driver::set($engine_pool, $key, $data, $ttl);
            }
        }

        /**
         * Set multiple records in cache
         *
         * @param string  $engine
         * @param string  $engine_pool
         * @param bool    $cache_engine_memory Use memory cache
         * @param array   $rows
         * @param integer $ttl seconds
         */
        public static function set_multi($engine, $engine_pool, $cache_engine_memory, array $rows, $ttl=null) {

            if (! $rows) {
                return;
            }

            // Memory
            if ($cache_engine_memory) {
                driver\memory::set_multi(null, $rows);
            }

            if ($engine) {
                $engine_driver = "\\neoform\\cache\\driver\\{$engine}";
                $engine_driver::set_multi($engine_pool, $rows, $ttl);
            }
        }

        /**
         * Get a record from cache
         *
         * @param string $engine
         * @param string $engine_pool
         * @param bool   $cache_engine_memory Use memory cache
         * @param string $key
         *
         * @return mixed|null
         */
        public static function get($engine, $engine_pool, $cache_engine_memory, $key) {

            // Memory
            if ($cache_engine_memory && driver\memory::exists(null, $key)) {
                return driver\memory::get(null, $key);
            }

            if ($engine) {
                $engine_driver = "\\neoform\\cache\\driver\\{$engine}";
                if ($data = $engine_driver::get($engine_pool, $key)) {
                    return reset($data);
                }
            }
        }

        /**
         * Increment the value of a cached entry (only works if the value is an int)
         *
         * @param string  $engine
         * @param string  $engine_pool
         * @param bool    $cache_engine_memory Use memory cache
         * @param string  $key
         * @param integer $offset
         */
        public static function increment($engine, $engine_pool, $cache_engine_memory, $key, $offset=1) {

            // Memory
            if ($cache_engine_memory) {
                driver\memory::increment(null, $key, $offset);
            }

            if ($engine) {
                $engine = "\\neoform\\cache\\driver\\{$engine}";
                $engine::increment($engine_pool, $key, $offset);
            }
        }

        /**
         * Decrement the value of a cached entry (only works if the value is an int)
         *
         * @param string  $engine
         * @param string  $engine_pool
         * @param bool    $cache_engine_memory Use memory cache
         * @param string  $key
         * @param integer $offset
         */
        public static function decrement($engine, $engine_pool, $cache_engine_memory, $key, $offset=1) {

            // Memory
            if ($cache_engine_memory) {
                driver\memory::decrement(null, $key, $offset);
            }

            if ($engine) {
                $engine = "\\neoform\\cache\\driver\\{$engine}";
                $engine::decrement($engine_pool, $key, $offset);
            }
        }

        /**
         * Checks cache for an entry, pulls from source $data_func() if not present in cache
         *
         * @param string       $engine              Which caching engines to use
         * @param string       $engine_pool_read    Caching pool
         * @param string       $engine_pool_write   Caching pool
         * @param bool         $cache_engine_memory Use memory cache
         * @param string       $key                 Cache key
         * @param callable     $data_func           Source data function
         * @param callable     $after_cache_func    After cache function
         * @param mixed|null   $args                Args to pass to $data_func($key [, array $args])
         * @param integer|null $ttl                 Cache length
         * @param bool         $cache_empty_results cache empty results (eg, null|false) if that is what $data_func() returns
         *
         * @return mixed returns the value from $data_func()
         */

        // re-arrange order of params, $key shouldn't be there.

        public static function single($engine, $engine_pool_read, $engine_pool_write, $cache_engine_memory,
                                      $key, callable $data_func, callable $after_cache_func=null, $args=null,
                                      $ttl=null, $cache_empty_results=true) {

            // Memory
            if ($cache_engine_memory && driver\memory::exists(null, $key)) {
                return driver\memory::get(null, $key);
            }

            if ($engine) {

                $engine_driver = "\\neoform\\cache\\driver\\{$engine}";

                if ($data = $engine_driver::get($engine_pool_read, $key)) {

                    // cache_driver::get() will return an array if a result was found in cache
                    $data = reset($data);

                    // Save to memory - for faster lookup if this record gets requested again
                    if ($cache_engine_memory) {
                        driver\memory::set(null, $key, $data);
                    }

                    return $data;
                }
            }

            // Not found in cache - get the data from it's original source
            $data = $data_func($args);

            if ($data !== null || $cache_empty_results) {

                //save to memory
                if ($cache_engine_memory) {
                    driver\memory::set(null, $key, $data);
                }

                // cache data to engine
                if ($engine) {

                    $engine_driver::set($engine_pool_write, $key, $data, $ttl);

                    if ($after_cache_func) {
                        $after_cache_func($key, $data);
                    }
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
         * @param string       $engine_pool_read    Which caching pool to use
         * @param string       $engine_pool_write   Which caching pool to use
         * @param bool         $cache_engine_memory Use memory cache
         * @param array        $rows                Rows to look up in cache
         * @param callable     $key_func            generates the cache key based on data from $rows
         * @param callable     $data_func           Source data function(array $keys [, array $args])
         * @param callable     $after_cache_func    After cache function
         * @param mixed|null   $args                args to pass to the $data_func
         * @param integer|null $ttl                 How long to cache
         * @param bool         $cache_empty_results cache empty results (eg, null|false) if that is what $data_func() returns
         *
         * @return array of mixed values from $data_func() calls
         */
        public static function multi($engine, $engine_pool_read, $engine_pool_write, $cache_engine_memory, array $rows,
                                     callable $key_func, callable $data_func, callable $after_cache_func=null,
                                     $args=null, $ttl=null, $cache_empty_results=true) {

            if (! $rows) {
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
             * PHP Memory
             */
            if ($cache_engine_memory && $found_in_memory = driver\memory::get_multi(null, $missing_rows)) {
                foreach ($found_in_memory as $index => $key) {
                    $matched_rows[$index] = $key;
                    unset($missing_rows[$index]);
                }
            }

            if (! $missing_rows) {
                return $matched_rows;
            }

            $rows_not_in_memory = $missing_rows;

            /**
             * Source Engine
             */
            if ($engine && $missing_rows) {
                $engine = "\\neoform\\cache\\driver\\{$engine}";
                foreach ($engine::get_multi($engine_pool_read, $missing_rows) as $key => $row) {
                    $matched_rows[$key] = $row;
                    unset($missing_rows[$key]);
                }
            }

            //Get any missing data from the origin
            if ($missing_rows) {

                // duplicate the array, so we can know what rows need to be stored in cache
                $rows_not_in_cache = $missing_rows;

                if ($origin_rows = $data_func(array_intersect_key($rows, $missing_rows), $missing_rows, $args)) {
                    foreach ($origin_rows as $key => $val) {
                        $matched_rows[$key] = $val;
                        unset($missing_rows[$key]);
                    }
                }

                // still missing? doesn't exist then.. null it
                if ($missing_rows) {
                    foreach (array_keys($missing_rows) as $index) {
                        $matched_rows[$index] = null;
                    }
                }
            } else {
                $rows_not_in_cache = null;
            }

            // Save to memory
            if ($cache_engine_memory && $rows_not_in_memory) {
                $save_to_memory = [];
                foreach (array_intersect_key($matched_rows, $rows_not_in_memory) as $index => $row) {
                    // either we cache empty results, or the row is not empty
                    $row = $matched_rows[$index];
                    if ($row !== null || $cache_empty_results) {
                        $save_to_memory[$key_lookup[$index]] = $row;
                    }
                }

                driver\memory::set_multi(null, $save_to_memory);
            }

            // Save to cache
            if ($engine && $rows_not_in_cache) {
                $save_to_cache = [];
                foreach (array_intersect_key($matched_rows, $rows_not_in_cache) as $index => $row) {
                    // either we cache empty results, or the row is not empty
                    if ($row !== null || $cache_empty_results) {
                        $save_to_cache[$key_lookup[$index]] = $row;
                    }
                }

                $engine::set_multi($engine_pool_write, $save_to_cache, $ttl);

                if ($after_cache_func) {
                    $after_cache_func(
                        $rows_not_in_cache,
                        array_intersect_key($rows, $rows_not_in_cache),
                        $matched_rows
                    );
                }
            }

            return $matched_rows;
        }

        /**
         * Delete a cache entry
         *
         * @param string $engine
         * @param string $engine_pool
         * @param bool   $cache_engine_memory Use memory cache
         * @param string $key
         */
        public static function delete($engine, $engine_pool, $cache_engine_memory, $key) {

            // Memory
            if ($cache_engine_memory) {
                driver\memory::delete(null, $key);
            }

            if ($engine) {
                $engine = "\\neoform\\cache\\driver\\{$engine}";
                $engine::delete($engine_pool, $key);
            }
        }

        /**
         * Delete multiple entries from cache
         *
         * @param string $engine
         * @param string $engine_pool
         * @param bool   $cache_engine_memory Use memory cache
         * @param array  $keys
         */
        public static function delete_multi($engine, $engine_pool, $cache_engine_memory, array $keys) {

            if ($keys) {

                // Memory
                if ($cache_engine_memory) {
                    driver\memory::delete_multi(null, $keys);
                }

                if ($engine) {
                    $engine = "\\neoform\\cache\\driver\\{$engine}";
                    $engine::delete_multi($engine_pool, $keys);
                }
            }
        }

        /**
         * Expire a cache entry
         *
         * @param string  $engine
         * @param string  $engine_pool
         * @param bool    $cache_engine_memory Use memory cache
         * @param string  $key
         * @param integer $ttl seconds to live
         */
        public static function expire($engine, $engine_pool, $cache_engine_memory, $key, $ttl) {

            // Memory
            if ($cache_engine_memory) {
                driver\memory::delete(null, $key);
            }

            if ($engine) {
                $engine = "\\neoform\\cache\\driver\\{$engine}";
                $engine::expire($engine_pool, $key, $ttl);
            }
        }

        /**
         * Delete multiple entries from cache
         *
         * @param string  $engine
         * @param string  $engine_pool
         * @param bool    $cache_engine_memory Use memory cache
         * @param array   $keys
         * @param integer $ttl seconds to live
         */
        public static function expire_multi($engine, $engine_pool, $cache_engine_memory, array $keys, $ttl) {

            if ($keys) {

                // Memory
                if ($cache_engine_memory) {
                    driver\memory::delete_multi(null, $keys);
                }

                if ($engine) {
                    $engine = "\\neoform\\cache\\driver\\{$engine}";
                    $engine::expire_multi($engine_pool, $keys, $ttl);
                }
            }
        }
    }