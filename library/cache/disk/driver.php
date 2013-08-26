<?php

    class cache_disk_driver implements cache_driver {

        /**
         * Activate a pipelined (batch) query - this doesn't do anything, so ignore
         *
         * @param string $pool
         */
        public static function pipeline_start($pool) {

        }

        /**
         * Execute pipelined (batch) queries and return result - this doesn't do anything, so ignore
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
         * @throws cache_disk_exception
         */
        public static function increment($pool, $key, $offset=1) {
            throw new cache_disk_exception('Disk cache does not support incrementing');
        }

        /**
         * Decrement the value of a cached entry (only works if the value is an int)
         *
         * @param string  $pool
         * @param string  $key
         * @param integer $offset
         * @throws cache_disk_exception
         */
        public static function decrement($pool, $key, $offset=1) {
            throw new cache_disk_exception('Disk cache does not support decrementing');
        }

        /**
         * Checks if a record exists
         *
         * @param string $pool
         * @param string $key
         *
         * @return array|null returns null if record does not exist.
         */
        public static function exists($pool, $key) {
            return (bool) cache_disk_dao::exists(cache_disk_dao::path($key));
        }

        /**
         * Create a list and/or Add a value to a list
         *
         * @param string $pool
         * @param string $key
         * @param mixed  $value
         *
         * @throws cache_disk_exception
         */
        public static function list_add($pool, $key, $value) {
            throw new cache_disk_exception('List commands are not supported by disk cache');
        }

        /**
         * Get all members of a list or get matching members of a list
         *
         * @param string $pool
         * @param string $key
         * @param array  $filter list of keys, an intersection is done
         *
         * @throws cache_disk_exception
         */
        public static function list_get($pool, $key, array $filter = null) {
            throw new cache_disk_exception('List commands are not supported by disk cache');
        }

        /**
         * Get all members of multiple list or get matching members of multiple lists (via filter array)
         *
         * @param string $pool
         * @param array  $keys
         * @param array  $filter list of keys, an intersection is done
         *
         * @throws cache_disk_exception
         */
        public static function list_get_union($pool, array $keys, array $filter = null) {
            throw new cache_disk_exception('List commands are not supported by disk cache');
        }

        /**
         * Remove values from a list
         *
         * @param string $pool
         * @param string $key
         * @param mixed  $remove_key
         *
         * @throws cache_disk_exception
         */
        public static function list_remove($pool, $key, $remove_key) {
            throw new cache_disk_exception('List commands are not supported by disk cache');
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
            try {
                return [
                    cache_disk_dao::get(cache_disk_dao::path($key)),
                ];
            } catch (cache_disk_exception $e) {

            }
        }

        /**
         * @param string       $pool
         * @param string       $key
         * @param mixed        $data
         * @param integer|null $ttl
         */
        public static function set($pool, $key, $data, $ttl=null) {
            cache_disk_dao::set(
                cache_disk_dao::path($key),
                $data,
                $ttl
            );
        }

        /**
         * Fetch multiple rows from disk
         *
         * @param array $pool
         * @param array $keys
         *
         * @return array
         */
        public static function get_multi($pool, array $keys) {
            $matched_rows = [];
            foreach ($keys as $index => $key) {
                try {
                    $matched_rows[$index] = cache_disk_dao::get(cache_disk_dao::path($key));
                    //unset($keys[$index]);
                } catch (cache_disk_exception $e) {

                }
            }

            return $matched_rows;
        }

        /**
         * Set multiple records in cache
         *
         * @param string $pool
         * @param array  $rows
         * @param null   $ttl
         */
        public static function set_multi($pool, array $rows, $ttl=null) {
            foreach ($rows as $key => $row) {
                cache_disk_dao::set(cache_disk_dao::path($key), $row, $ttl);
            }
        }

        /**
         * Delete a record from disk
         *
         * @param string $pool
         * @param string $key
         */
        public static function delete($pool, $key) {
            cache_disk_dao::del(cache_disk_dao::path($key));
        }

        /**
         * Delete multiple entries from cache
         *
         * @param string $pool
         * @param array $keys
         */
        public static function delete_multi($pool, array $keys) {

            if (count($keys)) {
                foreach ($keys as $key) {
                    cache_disk_dao::del(
                        cache_disk_dao::path($key)
                    );
                }
            }
        }

        /**
         * Expire a single record
         *
         * @param string  $pool
         * @param string  $key
         * @param integer $ttl how many seconds left for this key to live - if not set, it will expire now
         *
         * @throws cache_disk_exception
         */
        public static function expire($pool, $key, $ttl=0) {
            throw new cache_disk_exception('Expire commands are not supported by disk cache');
        }

        /**
         * Expire multiple entries
         *
         * @param string  $pool
         * @param array   $keys
         * @param integer $ttl how many seconds left for this key to live - if not set, it will expire now
         *
         * @throws cache_disk_exception
         */
        public static function expire_multi($pool, array $keys, $ttl=0) {
            throw new cache_disk_exception('Expire commands are not supported by disk cache');
        }
    }