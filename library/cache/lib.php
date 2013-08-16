<?php

    /**
     * Do not abort a script, even if the user stops loading the page
     * If a cache engine query terminates in the middle of cache deletion, that can cause damaged/dirty cache.
     */
    ignore_user_abort(1);

    /**
     * Caching library
     */
    class cache_lib {

        /**
         * Activate a pipelined (batch) query
         *
         * @param string $engine
         * @param string $engine_pool
         */
        public static function pipeline_start($engine, $engine_pool) {
            if ($engine) {
                $engine_driver = "cache_{$engine}_driver";
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
                $engine_driver = "cache_{$engine}_driver";
                return $engine_driver::pipeline_execute($engine_pool);
            }
        }

        /**
         * Set a record in cache
         *
         * @param string  $engine
         * @param string  $engine_pool
         * @param string  $key
         * @param mixed   $data
         * @param integer $ttl seconds
         *
         * @return mixed|null
         */
        public static function set($engine, $engine_pool, $key, $data, $ttl=null) {

            // Memory
            cache_memory_dao::set($key, $data);

            if ($engine) {
                $engine_driver = "cache_{$engine}_driver";
                $engine_driver::set($engine_pool, $key, $data, $ttl);
            }
        }

        /**
         * Set multiple records in cache
         *
         * @param string  $engine
         * @param string  $engine_pool
         * @param array   $rows
         * @param integer $ttl seconds
         */
        public static function set_multi($engine, $engine_pool, array $rows, $ttl=null) {

            // Memory
            cache_memory_dao::set_multi($rows);

            if ($engine) {
                $engine_driver = "cache_{$engine}_driver";
                $engine_driver::set_multi($engine_pool, $rows, $ttl);
            }
        }

        /**
         * Get a record from cache
         *
         * @param string $engine
         * @param string $engine_pool
         * @param string $key
         *
         * @return mixed|null
         */
        public static function get($engine, $engine_pool, $key) {

            // Memory
            if (cache_memory_dao::exists($key)) {
                return cache_memory_dao::get($key);
            }

            if ($engine) {
                $engine_driver = "cache_{$engine}_driver";
                if ($data = $engine_driver::get($engine_pool, $key)) {
                    return reset($data);
                }
            }
        }

        /**
         * Get a segment of a list/array
         *
         * @param string     $engine
         * @param string     $engine_pool
         * @param string     $list_key
         * @param array|null $filter
         *
         * @return array|null
         */
        public static function list_get($engine, $engine_pool, $list_key, array $filter=null) {

            // Memory
            // This cannot be used until it's properly set up. There are bugs that happen when a new list is created
            // before checking the cache driver (eg redis) to see if the list exists or not

            //if (cache_memory_dao::exists($list_key) && ($arr = cache_memory_dao::list_get($list_key, $filter)) !== null) {
            //    return $arr;
            //}

            if ($engine) {
                $engine_driver = "cache_{$engine}_driver";
                return $engine_driver::list_get($engine_pool, $list_key, $filter);
            }
        }

        /**
         * Get a segment of multiple joined lists/arrays (via union)
         *
         * @param string     $engine
         * @param string     $engine_pool
         * @param array      $list_keys
         * @param array|null $filter
         *
         * @return array|null
         */
        public static function list_get_union($engine, $engine_pool, array $list_keys, array $filter=null) {
            if ($engine) {
                $engine_driver = "cache_{$engine}_driver";
                return $engine_driver::list_get_union($engine_pool, $list_keys, $filter);
            }
        }

        /**
         * Create a list and/or Add a value to a list
         *
         * @param string $engine
         * @param string $list_key
         * @param string $engine_pool
         * @param mixed  $value
         */
        public static function list_add($engine, $engine_pool, $list_key, $value) {

            // Memory
            //cache_memory_dao::list_add($list_key, $value);

            if ($engine) {
                $engine = "cache_{$engine}_driver";
                $engine::list_add($engine_pool, $list_key, $value);
            }
        }

        /**
         * Remove values from a list
         *
         * @param string $engine
         * @param string $engine_pool
         * @param string $list_key
         * @param array  $remove_keys
         */
        public static function list_remove($engine, $engine_pool, $list_key, $remove_keys) {

            // Memory
            //cache_memory_dao::list_remove($list_key, $remove_keys);

            if ($engine) {
                $engine_driver = "cache_{$engine}_driver";
                $engine_driver::list_remove($engine_pool, $list_key, $remove_keys);
            }
        }

        /**
         * Increment the value of a cached entry (only works if the value is an int)
         *
         * @param string  $engine
         * @param string  $engine_pool
         * @param string  $key
         * @param integer $offset
         */
        public static function increment($engine, $engine_pool, $key, $offset=1) {

            // Memory
            cache_memory_dao::increment($key, $offset);

            if ($engine) {
                $engine = "cache_{$engine}_driver";
                $engine::increment($engine_pool, $key, $offset);
            }
        }

        /**
         * Decrement the value of a cached entry (only works if the value is an int)
         *
         * @param string  $engine
         * @param string  $engine_pool
         * @param string  $key
         * @param integer $offset
         */
        public static function decrement($engine, $engine_pool, $key, $offset=1) {

            // Memory
            cache_memory_dao::decrement($key, $offset);

            if ($engine) {
                $engine = "cache_{$engine}_driver";
                $engine::decrement($engine_pool, $key, $offset);
            }
        }

        /**
         * Checks cache for an entry, pulls from source $data_func() if not present in cache
         *
         * @param string       $engine              Which caching engines to use
         * @param string       $engine_pool_read    Caching pool
         * @param string       $engine_pool_write   Caching pool
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

        public static function single($engine, $engine_pool_read, $engine_pool_write, $key, callable $data_func,
                                      callable $after_cache_func=null, $args=null, $ttl=null, $cache_empty_results=true) {

            // Memory
            if (cache_memory_dao::exists($key)) {
                return cache_memory_dao::get($key);
            }

            $engine_driver = "cache_{$engine}_driver";

            if ($engine && $data = $engine_driver::get($engine_pool_read, $key)) {
                $data = reset($data);
            } else {
                //get the data from it's original source
                $data = $data_func($args);
            }

            if ($data !== null || $cache_empty_results) {

                //save to memory (always)
                cache_memory_dao::set($key, $data);

                // cache data to engine
                if ($engine) {
                    $engine_driver::set($engine_pool_write, $key, $data, $ttl);

                    if ($after_cache_func) {
                        $after_cache_func($key);
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
        public static function multi($engine, $engine_pool_read, $engine_pool_write, array $rows, callable $key_func,
                                     callable $data_func, callable $after_cache_func=null, $args=null, $ttl=null, $cache_empty_results=true) {

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
            if ($found_in_memory = cache_memory_dao::get_multi($missing_rows)) {
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
                $engine = "cache_{$engine}_driver";
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
            if ($engine && $rows_not_in_cache) {
                $save_to_cache = [];
                foreach (array_keys($rows_not_in_cache) as $index) {
                    // either we cache empty results, or the row is not empty
                    $row = $matched_rows[$index];
                    if ($row !== null || $cache_empty_results) {
                        $save_to_cache[$key_lookup[$index]] = $row;
                    }
                }

                $engine::set_multi($engine_pool_write, $save_to_cache, $ttl);

                if ($after_cache_func) {
                    $after_cache_func(array_intersect_key($rows, $rows_not_in_cache));
                }
            }

            return $matched_rows;
        }

        /**
         * Delete a cache entry
         *
         * @param string $engine
         * @param string $engine_pool
         * @param string $key
         */
        public static function delete($engine, $engine_pool, $key) {

            // Memory
            cache_memory_dao::delete($key);

            if ($engine) {
                $engine = "cache_{$engine}_driver";
                $engine::delete($engine_pool, $key);
            }
        }

        /**
         * Delete multiple entries from cache
         *
         * @param string $engine
         * @param string $engine_pool
         * @param array  $keys
         */
        public static function delete_multi($engine, $engine_pool, array $keys) {

            if (count($keys)) {

                // Memory
                foreach ($keys as $key) {
                    cache_memory_dao::delete($key);
                }

                if ($engine) {
                    $engine = "cache_{$engine}_driver";
                    $engine::delete_multi($engine_pool, $keys);
                }
            }
        }

        /**
         * Expire a cache entry
         *
         * @param string  $engine
         * @param string  $engine_pool
         * @param string  $key
         * @param integer $ttl seconds to live
         */
        public static function expire($engine, $engine_pool, $key, $ttl) {

            // Memory
            cache_memory_dao::delete($key);

            if ($engine) {
                $engine = "cache_{$engine}_driver";
                $engine::expire($engine_pool, $key, $ttl);
            }
        }

        /**
         * Delete multiple entries from cache
         *
         * @param string  $engine
         * @param string  $engine_pool
         * @param array   $keys
         * @param integer $ttl seconds to live
         */
        public static function expire_multi($engine, $engine_pool, array $keys, $ttl) {

            if (count($keys)) {

                // Memory
                foreach ($keys as $key) {
                    cache_memory_dao::delete($key);
                }

                if ($engine) {
                    $engine = "cache_{$engine}_driver";
                    $engine::expire_multi($engine_pool, $keys, $ttl);
                }
            }
        }

        /**
         * Delete all cache entries being stored by a list (from the list as well), by applying filters
         *
         * @param string            $engine
         * @param string            $engine_pool
         * @param string            $list_key
         * @param string|array|null $filter
         */
        public static function delete_cache_filter_list($engine, $engine_pool, $list_key, $filter=null) {

            $keys = self::list_get(
                $engine,
                $engine_pool,
                $list_key
            );

            if ($keys) {
                if ($filter !== null) {
                    if (is_array($filter)) {
                        $keys_matched = [];
                        foreach ($filter as $f) {
                            foreach (preg_grep('`' . preg_quote($f) . '`', $keys) as $key) {
                                $keys_matched[] = $key;
                            }
                        }
                    } else {
                        $keys_matched = preg_grep('`' . preg_quote($filter) . '`', $keys);
                    }

                    // If there are any relevant keys in the list to remove, remove them
                    if ($keys_matched) {
                        $keys = $keys_matched;

                    // Otherwise return
                    } else {
                        return;
                    }
                }

                // We do not remove the list_key, because there's a race condition here
                // It's possible for the list to have newly added keys, right before it gets deleted
                // which would result in lost cache keys that exist but are not on the list any more.
                // The downside to not deleting this, is the cache key is basically permanent.
                // However, there is only one such key per entity, so it's not a big deal.

                self::pipeline_start($engine, $engine_pool);

                // Even though these commands are pipelined, they are not necessarily atomic (as is the case with redis)
                // So we delete from the list before we delete from the actual cache key, incase that cache key somehow
                // gets re-added before it was removed from the list. (race condition, same as above)

                // Remove the all keys from the list
                self::list_remove(
                    $engine,
                    $engine_pool,
                    $list_key,
                    $keys
                );

                // Delete keys
                self::delete_multi(
                    $engine,
                    $engine_pool,
                    $keys
                );

                self::pipeline_execute($engine, $engine_pool);
            }
        }
    }