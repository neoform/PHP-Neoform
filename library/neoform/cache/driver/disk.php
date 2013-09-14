<?php

    namespace neoform\cache\driver;

    class disk implements \neoform\cache\driver {

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
         * @throws disk\exception
         */
        public static function increment($pool, $key, $offset=1) {
            throw new disk\exception('Disk cache does not support incrementing');
        }

        /**
         * Decrement the value of a cached entry (only works if the value is an int)
         *
         * @param string  $pool
         * @param string  $key
         * @param integer $offset
         * @throws disk\exception
         */
        public static function decrement($pool, $key, $offset=1) {
            throw new disk\exception('Disk cache does not support decrementing');
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
            return (bool) disk\dao::exists(disk\dao::path($key));
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
                    disk\dao::get(disk\dao::path($key)),
                ];
            } catch (disk\exception $e) {

            }
        }

        /**
         * @param string       $pool
         * @param string       $key
         * @param mixed        $data
         * @param integer|null $ttl
         */
        public static function set($pool, $key, $data, $ttl=null) {
            disk\dao::set(
                disk\dao::path($key),
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
                    $matched_rows[$index] = disk\dao::get(disk\dao::path($key));
                    //unset($keys[$index]);
                } catch (disk\exception $e) {

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
                disk\dao::set(disk\dao::path($key), $row, $ttl);
            }
        }

        /**
         * Delete a record from disk
         *
         * @param string $pool
         * @param string $key
         */
        public static function delete($pool, $key) {
            disk\dao::del(disk\dao::path($key));
        }

        /**
         * Delete multiple entries from cache
         *
         * @param string $pool
         * @param array $keys
         */
        public static function delete_multi($pool, array $keys) {

            if ($keys) {
                foreach ($keys as $key) {
                    disk\dao::del(
                        disk\dao::path($key)
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
         * @throws disk\exception
         */
        public static function expire($pool, $key, $ttl=0) {
            throw new disk\exception('Expire commands are not supported by disk cache');
        }

        /**
         * Expire multiple entries
         *
         * @param string  $pool
         * @param array   $keys
         * @param integer $ttl how many seconds left for this key to live - if not set, it will expire now
         *
         * @throws disk\exception
         */
        public static function expire_multi($pool, array $keys, $ttl=0) {
            throw new disk\exception('Expire commands are not supported by disk cache');
        }
    }