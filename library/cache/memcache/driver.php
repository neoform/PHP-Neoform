<?php

    class cache_memcache_driver implements cache_driver {

        /**
         * Activate a pipelined (batch) query - this doesn't exist in memcache, so ignore
         *
         * @param string $pool
         */
        public static function pipeline_start($pool) {

        }

        /**
         * Execute pipelined (batch) queries and return result - this doesn't exist in memcache, so ignore
         *
         * @param string $pool
         */
        public static function pipeline_execute($pool) {

        }

        /**
         * Increment the value of a cached entry (only works if the value is an int)
         *
         * @param string  $pool
         * @param string  $key
         * @param integer $offset
         */
        public static function increment($pool, $key, $offset=1) {
            core::memcache($pool)->increment($key, $offset);
        }

        /**
         * Decrement the value of a cached entry (only works if the value is an int)
         *
         * @param string  $pool
         * @param string  $key
         * @param integer $offset
         */
        public static function decrement($pool, $key, $offset=1) {
            core::memcache($pool)->decrement($key, $offset);
        }

        /**
         * Checks if cached record exists.
         *
         * @param string $pool
         * @param string $key
         *
         * @return boolean
         */
        public static function exists($pool, $key) {
            $memcache = core::memcache($pool);
            $memcache->get($key);
            return (bool) $memcache->row_found();
        }

        /**
         * Create a list and/or Add a value to a list
         *
         * @param string $pool
         * @param string $key
         * @param mixed  $value
         *
         * @throws cache_memcache_exception
         */
        public static function list_add($pool, $key, $value) {
            throw new cache_memcache_exception('List commands are not supported by memcache');
        }

        /**
         * Get all members of a list or get matching members of a list
         *
         * @param string $pool
         * @param string $key
         * @param array  $filter list of keys, an intersection is done
         *
         * @throws cache_memcache_exception
         */
        public static function list_get($pool, $key, array $filter = null) {
            throw new cache_memcache_exception('List commands are not supported by memcache');
        }

        /**
         * Get all members of multiple list or get matching members of multiple lists (via filter array)
         *
         * @param string $pool
         * @param array  $keys
         * @param array  $filter list of keys, an intersection is done
         *
         * @throws cache_memcache_exception
         */
        public static function list_get_union($pool, array $keys, array $filter = null) {
            throw new cache_memcache_exception('List commands are not supported by memcache');
        }

        /**
         * Remove values from a list
         *
         * @param string $pool
         * @param string $key
         * @param array  $remove_keys
         *
         * @throws cache_memcache_exception
         */
        public static function list_remove($pool, $key, array $remove_keys) {
            throw new cache_memcache_exception('List commands are not supported by memcache');
        }

        /**
         * Gets cached data.
         *  if record does exist, an array with a single element, containing the data.
         *  returns null if record does not exist
         *
         * @param string $pool
         * @param string $key
         *
         * @return array|null returns null if record does not exist.
         */
        public static function get($pool, $key) {
            $memcache = core::memcache($pool);
            $data = $memcache->get($key);
            if ($memcache->row_found()) {
                return [
                    $data,
                ];
            }
        }

        /**
         * @param string       $pool
         * @param string       $key
         * @param mixed        $data
         * @param integer|null $ttl
         *
         * @return mixed
         */
        public static function set($pool, $key, $data, $ttl=null) {
            return core::memcache($pool)->set(
                $key,
                $data,
                $ttl
            );
        }

        /**
         * Fetch multiple rows from memcached
         *
         * @param string $pool
         * @param array  $keys
         *
         * @return array
         */
        public static function get_multi($pool, array $keys) {
            return core::memcache($pool)->getMulti($keys);
        }

        /**
         * Set multiple records at the same time
         *
         * @param string       $pool
         * @param array        $rows
         * @param integer|null $ttl
         *
         * @return mixed
         */
        public static function set_multi($pool, array $rows, $ttl=null) {
            return core::memcache($pool)->setMulti($rows, $ttl);
        }

        /**
         * Delete a single record
         *
         * @param string $pool
         * @param string $key
         */
        public static function delete($pool, $key) {
            core::memcache($pool)->delete($key);
        }

        /**
         * Delete multiple entries from cache
         *
         * @param string $pool
         * @param array  $keys
         */
        public static function delete_multi($pool, array $keys) {
            if (count($keys)) {
                $mc = core::memcache($pool);
                foreach ($keys as $key) {
                    $mc->delete($key);
                }
            }
        }
    }