<?php

    class cache_memcache_driver implements cache_driver {

        /**
         * @var The prefix used before all keys
         */
        protected static $key_prefix;

        /**
         * Gets the prefix from the configs and saves it locally
         *    forced to use this since the built in memcached OPT_PREFIX_KEY doesn't seem to work. :(
         *
         * @return string
         */
        public static function key_prefix() {
            if (self::$key_prefix === null) {
                self::$key_prefix = core::config()->memcache['key_prefix'] . ':';
            }
            return self::$key_prefix;
        }

        /**
         * Increment the value of a cached entry (only works if the value is an int)
         *
         * @param string  $key
         * @param string  $pool
         * @param integer $offset
         */
        public static function increment($key, $pool, $offset=1) {
            core::cache_memcache($pool)->increment(self::key_prefix() . $key, $offset);
        }

        /**
         * Decrement the value of a cached entry (only works if the value is an int)
         *
         * @param string  $key
         * @param string  $pool
         * @param integer $offset
         */
        public static function decrement($key, $pool, $offset=1) {
            core::cache_memcache($pool)->decrement(self::key_prefix() . $key, $offset);
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
            $memcache = core::cache_memcache($pool);
            $memcache->get(self::key_prefix() . $key);
            return (bool) $memcache->row_found();
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
            $memcache = core::cache_memcache($pool);
            $data = $memcache->get(self::key_prefix() . $key);
            if ($memcache->row_found()) {
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
            return core::cache_memcache($pool)->set(
                self::key_prefix() . $key,
                $data,
                $ttl
            );
        }

        /**
         * Fetch multiple rows from memcached
         *
         * @param array  $keys
         * @param string $pool
         *
         * @return array
         */
        public static function get_multi(array $keys, $pool) {

            $prefix = self::key_prefix();

            $mc_keys = [];
            foreach ($keys as $index => $key) {
                $mc_keys[$index] = $prefix . $key;
            }

            $found_rows = core::cache_memcache($pool)->getMulti($mc_keys);

            $matched_rows = [];
            if ($found_rows && count($found_rows)) {
                // need to run this backwards, since all the keys have been prefixed with $prefix
                foreach ($keys as $index => $key) {
                    if (isset($found_rows[$prefix . $key])) {
                        $matched_rows[$index] = $found_rows[$prefix . $key];
                        //unset($keys[$index]);
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

            $prefix = self::key_prefix();
            $set    = [];

            foreach ($rows as $key => $row) {
                $set[$prefix . $key] = $row;
            }

            return core::cache_memcache($pool)->setMulti($set, $ttl);
        }

        /**
         * Get all keys matching a query - this is not supported by memcache
         *
         * @param string $key
         * @param string $pool
         *
         * @return array|null returns null if record does not exist.
         * @throws cache_memcache_exception
         */
        public static function get_wildcard($key, $pool) {
            throw new cache_memcache_exception('Wildcard lookups are not supported by memcache');
        }

        /**
         * Delete a single record
         *
         * @param string $key
         * @param string $pool
         */
        public static function delete($key, $pool) {
            core::cache_memcache($pool)->delete(self::key_prefix() . $key);
        }

        /**
         * Delete multiple entries from cache
         *
         * @param array  $keys
         * @param string $pool
         */
        public static function delete_multi(array $keys, $pool) {
            if (count($keys)) {
                reset($keys);
                $mc = core::cache_memcache($pool);
                $prefix_mc = self::key_prefix();
                foreach ($keys as $key) {
                    $mc->delete($prefix_mc . $key);
                }
            }
        }

        /**
         * Delete all keys matching a query
         *
         * @param string $key
         * @param string $pool
         * @throws cache_memcache_exception
         */
        public static function delete_wildcard($key, $pool) {
            throw new cache_memcache_exception('Wildcard lookups are not supported by memcache');
        }

        /**
         * Delete all keys matching multiple queries
         *
         * @param array  $keys
         * @param string $pool
         * @throws cache_memcache_exception
         */
        public static function delete_wildcard_multi(array $keys, $pool) {
            throw new cache_memcache_exception('Wildcard lookups are not supported by memcache');
        }
    }