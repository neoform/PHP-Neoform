<?php

    namespace neoform\cache\disk;

    use neoform;

    class driver implements neoform\cache\driver {

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
         * @throws exception
         */
        public static function increment($pool, $key, $offset=1) {
            throw new exception('Disk cache does not support incrementing');
        }

        /**
         * Decrement the value of a cached entry (only works if the value is an int)
         *
         * @param string  $pool
         * @param string  $key
         * @param integer $offset
         * @throws exception
         */
        public static function decrement($pool, $key, $offset=1) {
            throw new exception('Disk cache does not support decrementing');
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
            return (bool) dao::exists(dao::path($key));
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
                    dao::get(dao::path($key)),
                ];
            } catch (exception $e) {

            }
        }

        /**
         * @param string       $pool
         * @param string       $key
         * @param mixed        $data
         * @param integer|null $ttl
         */
        public static function set($pool, $key, $data, $ttl=null) {
            dao::set(
                dao::path($key),
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
                    $matched_rows[$index] = dao::get(dao::path($key));
                    //unset($keys[$index]);
                } catch (exception $e) {

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
                dao::set(dao::path($key), $row, $ttl);
            }
        }

        /**
         * Delete a record from disk
         *
         * @param string $pool
         * @param string $key
         */
        public static function delete($pool, $key) {
            dao::del(dao::path($key));
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
                    dao::del(
                        dao::path($key)
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
         * @throws exception
         */
        public static function expire($pool, $key, $ttl=0) {
            throw new exception('Expire commands are not supported by disk cache');
        }

        /**
         * Expire multiple entries
         *
         * @param string  $pool
         * @param array   $keys
         * @param integer $ttl how many seconds left for this key to live - if not set, it will expire now
         *
         * @throws exception
         */
        public static function expire_multi($pool, array $keys, $ttl=0) {
            throw new exception('Expire commands are not supported by disk cache');
        }
    }