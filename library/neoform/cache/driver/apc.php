<?php

    namespace neoform\cache\driver;

    use neoform;

    class apc implements neoform\cache\driver {

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
                neoform\core::apc()->get($key);
                return true;
            } catch (neoform\apc\exception $e) {
                return false;
            }
        }

        /**
         * Increment the value of a cached entry (only works if the value is an int)
         *
         * @param string  $pool
         * @param string  $key
         * @param integer $offset
         */
        public static function increment($pool, $key, $offset=1){
            neoform\core::apc()->increment($key, $offset);
        }

        /**
         * Decrement the value of a cached entry (only works if the value is an int)
         *
         * @param string  $pool
         * @param string  $key
         * @param integer $offset
         */
        public static function decrement($pool, $key, $offset=1){
            neoform\core::apc()->decrement($key, $offset);
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
                    neoform\core::apc()->get($key),
                ];
            } catch (neoform\apc\exception $e) {

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
            return neoform\core::apc()->set($key, $data, $ttl);
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

            $apc = neoform\core::apc();

            $matched_rows = [];
            foreach ($keys as $index => $key) {
                try {
                    $matched_rows[$index] = $apc->get($key);
                    //unset($keys[$index]);
                } catch (neoform\apc\exception $e) {

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
            $apc = neoform\core::apc();
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
            neoform\core::apc()->del($key);
        }

        /**
         * Delete multiple entries from cache
         *
         * @param string $pool
         * @param array  $keys
         */
        public static function delete_multi($pool, array $keys) {
            if ($keys) {
                $apc = neoform\core::apc();
                foreach ($keys as $key) {
                    $apc->del($key);
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
         * @throws apc\exception
         */
        public static function expire($pool, $key, $ttl=0) {
            throw new apc\exception('Expire commands are not supported by APC');
        }

        /**
         * Expire multiple entries
         *
         * @param string  $pool
         * @param array   $keys
         * @param integer $ttl how many seconds left for this key to live - if not set, it will expire now
         *
         * @throws apc\exception
         */
        public static function expire_multi($pool, array $keys, $ttl=0) {
            throw new apc\exception('Expire commands are not supported by APC');
        }
    }