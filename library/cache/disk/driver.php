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
         * @param string  $key
         * @param string  $pool
         * @param integer $offset
         * @throws cache_disk_exception
         */
        public static function increment($key, $pool, $offset=1) {
            throw new cache_disk_exception('Disk cache does not support incrementing');
        }

        /**
         * Decrement the value of a cached entry (only works if the value is an int)
         *
         * @param string  $key
         * @param string  $pool
         * @param integer $offset
         * @throws cache_disk_exception
         */
        public static function decrement($key, $pool, $offset=1) {
            throw new cache_disk_exception('Disk cache does not support decrementing');
        }

        /**
         * Checks if a record exists
         *
         * @param string $key
         * @param string $pool
         *
         * @return array|null returns null if record does not exist.
         */
        public static function exists($key, $pool) {
            return (bool) cache_disk_dao(cache_disk_dao::path($key));
        }

        /**
         * Create a list and/or Add a value to a list
         *
         * @param string $key
         * @param string $pool
         * @param mixed  $value
         *
         * @throws cache_disk_exception
         */
        public static function list_add($key, $pool, $value) {
            throw new cache_disk_exception('List commands are not supported by disk cache');
        }

        /**
         * Get all members of a list or get matching members of a list
         *
         * @param string $key
         * @param string $pool
         * @param array  $filter list of keys, an intersection is done
         *
         * @throws cache_disk_exception
         */
        public static function list_get($key, $pool, array $filter = null) {
            throw new cache_disk_exception('List commands are not supported by disk cache');
        }

        /**
         * Remove values from a list
         *
         * @param string $key
         * @param string $pool
         * @param array  $remove_keys
         *
         * @throws cache_disk_exception
         */
        public static function list_remove($key, $pool, array $remove_keys) {
            throw new cache_disk_exception('List commands are not supported by disk cache');
        }

        /**
         * Gets cached data.
         *  if record does exist, an array with a single element, containing the data.
         *  returns null if record does not exist
         *
         * @param string $key
         * @param string $pool
         *
         * @return array|null returns null if record does not exist.
         */
        public static function get($key, $pool) {
            try {
                return [
                    cache_disk_dao::get(cache_disk_dao::path($key)),
                ];
            } catch (cache_disk_exception $e) {

            }
        }

        /**
         * @param string       $key
         * @param string       $pool
         * @param mixed        $data
         * @param integer|null $ttl
         *
         * @return mixed
         */
        public static function set($key, $pool, $data, $ttl=null) {
            return cache_disk_dao::set(
                cache_disk_dao::path($key),
                $data,
                $ttl
            );
        }

        /**
         * Fetch multiple rows from disk
         *
         * @param array $keys
         * @param array $pool
         *
         * @return array
         */
        public static function get_multi(array $keys, $pool) {
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
         * @param array  $rows
         * @param string $pool
         * @param null   $ttl
         */
        public static function set_multi(array $rows, $pool, $ttl=null) {
            foreach ($rows as $key => $row) {
                cache_disk_dao::set(cache_disk_dao::path($key), $row, $ttl);
            }
        }

        /**
         * Delete a record from disk
         *
         * @param string $key
         * @param string $pool
         */
        public static function delete($key, $pool) {
            cache_disk_dao::del(cache_disk_dao::path($key));
        }

        /**
         * Delete multiple entries from cache
         *
         * @param array $keys
         * @param string $pool
         */
        public static function delete_multi(array $keys, $pool){

            if (count($keys)) {
                foreach ($keys as $key) {
                    cache_disk_dao::del(
                        cache_disk_dao::path($key)
                    );
                }
            }
        }
    }