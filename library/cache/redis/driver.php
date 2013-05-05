<?php

    class cache_redis_driver implements cache_driver {

        /**
         * Increment the value of a cached entry (only works if the value is an int)
         *
         * @param string  $key
         * @param string  $pool
         * @param integer $offset
         */
        public static function increment($key, $pool, $offset=1) {
            core::cache_redis($pool)->increment($key, $offset);
        }

        /**
         * Decrement the value of a cached entry (only works if the value is an int)
         *
         * @param string  $key
         * @param string  $pool
         * @param integer $offset
         */
        public static function decrement($key, $pool, $offset=1) {
            core::cache_redis($pool)->decrement($key, $offset);
        }

        /**
         * Checks if cached record exists.
         *
         * @param string $key
         * @param string $pool
         *
         * @return boolean
         */
        public static function exists($key, $pool) {
            return (bool) core::cache_redis($pool)->exists($key);
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
            $redis = core::cache_redis($pool);
            $data = $redis->get($key);
            if ($redis->row_found()) {
                return [
                    $data,
                ];
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
            return core::cache_redis($pool)->set(
                $key,
                $data,
                $ttl
            );
        }

        /**
         * Fetch multiple rows from redisd
         *
         * @param array  $keys
         * @param string $pool
         *
         * @return array
         */
        public static function get_multi(array $keys, $pool) {

            // @todo this can be cleaned up a bunch

            $mc_keys = [];
            foreach ($keys as $index => $key) {
                $mc_keys[$index] = $key;
            }

            $found_rows = core::cache_redis($pool)->getMulti($mc_keys);

            $matched_rows = [];
            if ($found_rows && count($found_rows)) {
                // need to run this backwards, since all the keys have been prefixed with $prefix
                foreach ($keys as $index => $key) {
                    if (isset($found_rows[$key])) {
                        $matched_rows[$index] = $found_rows[$key];
                    }
                }
            }

            return $matched_rows;
        }

        /**
         * Set multiple records at the same time
         *
         * @param array        $rows
         * @param string       $pool
         * @param integer|null $ttl
         *
         * @return mixed
         */
        public static function set_multi(array $rows, $pool, $ttl=null) {
            return core::cache_redis($pool)->setMulti($rows, $ttl);
        }

        /**
         * Delete a single record
         *
         * @param string $key
         * @param string $pool
         *
         * @return integer the number of keys deleted
         */
        public static function delete($key, $pool) {
            return core::cache_redis($pool)->delete($key);
        }

        /**
         * Delete multiple entries from cache
         *
         * @param array  $keys
         * @param string $pool
         *
         * @return integer the number of keys deleted
         */
        public static function delete_multi(array $keys, $pool) {
            if (count($keys)) {
                reset($keys);
                return core::cache_redis($pool)->delete($keys);
            }
        }
    }