<?php

    class cache_apc_driver implements cache_driver {

        /**
         * @var The prefix used before all keys
         */
        protected static $key_prefix;

        /**
         * Gets the prefix from the configs and saves it locally
         *
         * @return string
         */
        public static function key_prefix() {
            if (self::$key_prefix === null) {
                self::$key_prefix = core::config()->apc['key_prefix'] . ':';
            }
            return self::$key_prefix;
        }

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
         * Checks to see if a record exists
         *
         * @param string $key
         * @param string $pool
         *
         * @return bool
         */
        public static function exists($key, $pool) {
            try {
                core::cache_apc()->get(self::key_prefix() . $key);
                return true;
            } catch (cache_apc_exception $e) {
                return false;
            }
        }

        /**
         * Create a list and/or Add a value to a list
         *
         * @param string $key
         * @param string $pool
         * @param mixed  $value
         *
         * @throws cache_apc_exception
         */
        public static function list_add($key, $pool, $value) {
            throw new cache_apc_exception('List commands are not supported by APC');
        }

        /**
         * Get all members of a list or get matching members of a list
         *
         * @param string $key
         * @param string $pool
         * @param array  $filter list of keys, an intersection is done
         *
         * @throws cache_apc_exception
         */
        public static function list_get($key, $pool, array $filter = null) {
            throw new cache_apc_exception('List commands are not supported by APC');
        }

        /**
         * Remove values from a list
         *
         * @param string $key
         * @param string $pool
         * @param array  $remove_keys
         *
         * @throws cache_apc_exception
         */
        public static function list_remove($key, $pool, array $remove_keys) {
            throw new cache_apc_exception('List commands are not supported by APC');
        }

        /**
         * Increment the value of a cached entry (only works if the value is an int)
         *
         * @param string  $key
         * @param string  $pool
         * @param integer $offset
         */
        public static function increment($key, $pool, $offset=1){
            core::cache_apc()->increment(self::key_prefix() . $key, $offset);
        }

        /**
         * Decrement the value of a cached entry (only works if the value is an int)
         *
         * @param string  $key
         * @param string  $pool
         * @param integer $offset
         */
        public static function decrement($key, $pool, $offset=1){
            core::cache_apc()->decrement(self::key_prefix() . $key, $offset);
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
                    core::cache_apc()->get(self::key_prefix() . $key),
                ];
            } catch (cache_apc_exception $e) {

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
            return core::cache_apc()->set(self::key_prefix() . $key, $data, $ttl);
        }

        /**
         * Fetch multiple rows from apc
         *
         * @param array  $keys
         * @param string $pool
         *
         * @return array
         */
        public static function get_multi(array $keys, $pool) {

            $prefix_apc = self::key_prefix();
            $apc        = core::cache_apc();

            $matched_rows = [];
            foreach ($keys as $index => $key) {
                try {
                    $matched_rows[$index] = $apc->get($prefix_apc . $key);
                    //unset($keys[$index]);
                } catch (cache_apc_exception $e) {

                }
            }

            return $matched_rows;
        }

        /**
         * Set multiple records in APC
         *
         * @param array        $rows
         * @param string       $pool
         * @param integer|null $ttl
         */
        public static function set_multi(array $rows, $pool, $ttl=null) {
            $prefix = self::key_prefix();
            $apc    = core::cache_apc();
            foreach ($rows as $key => $row) {
                $apc->set($prefix . $key, $row, $ttl);
            }
        }

        /**
         * Delete a record from APC
         *
         * @param string $key
         * @param string $pool
         */
        public static function delete($key, $pool) {
            core::cache_apc()->del(self::key_prefix() . $key);
        }

        /**
         * Delete multiple entries from cache
         *
         * @param array  $keys
         * @param string $pool
         */
        public static function delete_multi(array $keys, $pool){

            if (count($keys)) {
                $apc = core::cache_apc();
                $prefix = self::key_prefix();
                foreach ($keys as $key) {
                    $apc->del($prefix . $key);
                }
            }
        }
    }