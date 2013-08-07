<?php

    class cache_apc_driver implements cache_driver {

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
         * @param string $pool
         * @param string $key
         *
         * @return bool
         */
        public static function exists($pool, $key) {
            try {
                core::apc()->get($key);
                return true;
            } catch (apc_exception $e) {
                return false;
            }
        }

        /**
         * Create a list and/or Add a value to a list
         *
         * @param string $pool
         * @param string $key
         * @param mixed  $value
         *
         * @throws cache_apc_exception
         */
        public static function list_add($pool, $key, $value) {
            throw new cache_apc_exception('List commands are not supported by APC');
        }

        /**
         * Get all members of a list or get matching members of a list
         *
         * @param string $pool
         * @param string $key
         * @param array  $filter list of keys, an intersection is done
         *
         * @throws cache_apc_exception
         */
        public static function list_get($pool, $key, array $filter = null) {
            throw new cache_apc_exception('List commands are not supported by APC');
        }

        /**
         * Get all members of multiple list or get matching members of multiple lists (via filter array)
         *
         * @param string $pool
         * @param array  $keys
         * @param array  $filter list of keys, an intersection is done
         *
         * @throws cache_apc_exception
         */
        public static function list_get_union($pool, array $keys, array $filter = null) {
            throw new cache_apc_exception('List commands are not supported by APC');
        }

        /**
         * Remove values from a list
         *
         * @param string $pool
         * @param string $key
         * @param array  $remove_keys
         *
         * @throws cache_apc_exception
         */
        public static function list_remove($pool, $key, array $remove_keys) {
            throw new cache_apc_exception('List commands are not supported by APC');
        }

        /**
         * Increment the value of a cached entry (only works if the value is an int)
         *
         * @param string  $pool
         * @param string  $key
         * @param integer $offset
         */
        public static function increment($pool, $key, $offset=1){
            core::apc()->increment($key, $offset);
        }

        /**
         * Decrement the value of a cached entry (only works if the value is an int)
         *
         * @param string  $pool
         * @param string  $key
         * @param integer $offset
         */
        public static function decrement($pool, $key, $offset=1){
            core::apc()->decrement($key, $offset);
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
                    core::apc()->get($key),
                ];
            } catch (cache_apc_exception $e) {

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
            return core::apc()->set($key, $data, $ttl);
        }

        /**
         * Fetch multiple rows from apc
         *
         * @param string $pool
         * @param array  $keys
         *
         * @return array
         */
        public static function get_multi($pool, array $keys) {

            $apc = core::apc();

            $matched_rows = [];
            foreach ($keys as $index => $key) {
                try {
                    $matched_rows[$index] = $apc->get($key);
                    //unset($keys[$index]);
                } catch (apc_exception $e) {

                }
            }

            return $matched_rows;
        }

        /**
         * Set multiple records in APC
         *
         * @param string       $pool
         * @param array        $rows
         * @param integer|null $ttl
         */
        public static function set_multi($pool, array $rows, $ttl=null) {
            $apc = core::apc();
            foreach ($rows as $key => $row) {
                $apc->set($key, $row, $ttl);
            }
        }

        /**
         * Delete a record from APC
         *
         * @param string $pool
         * @param string $key
         */
        public static function delete($pool, $key) {
            core::apc()->del($key);
        }

        /**
         * Delete multiple entries from cache
         *
         * @param string $pool
         * @param array  $keys
         */
        public static function delete_multi($pool, array $keys) {
            if (count($keys)) {
                $apc = core::apc();
                foreach ($keys as $key) {
                    $apc->del($key);
                }
            }
        }
    }