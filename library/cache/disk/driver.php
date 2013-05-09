<?php

    class cache_disk_driver implements cache_driver {

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
         * Get all keys matching a query - not supported
         *
         * @param string $key
         * @param string $pool
         *
         * @return array|null returns null if record does not exist.
         * @throws cache_disk_exception
         */
        public static function get_wildcard($key, $pool) {
            throw new cache_disk_exception('Wildcard lookups are not supported by disk cache');
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

        /**
         * Delete all keys matching a query - not supported
         *
         * @param string $key
         * @param string $pool
         * @throws cache_disk_exception
         */
        public static function delete_wildcard($key, $pool) {
            throw new cache_disk_exception('Wildcard lookups are not supported by disk cache');
        }

        /**
         * Delete all keys matching multiple queries - not supported
         *
         * @param array  $keys
         * @param string $pool
         * @throws cache_disk_exception
         */
        public static function delete_wildcard(array $keys, $pool) {
            throw new cache_disk_exception('Wildcard lookups are not supported by disk cache');
        }
    }