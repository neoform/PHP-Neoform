<?php

    namespace neoform\cache\memory;

    /**
     * Cache variables in memory
     */
    class dao {

        /**
         * @var array holds the cache
         */
        private static $local = [];

        /**
         * Checks to see if a key exists in cache
         *
         * @param string $k key
         *
         * @return bool
         */
        public static function exists($k) {
            return array_key_exists($k, self::$local);
        }

        /**
         * Caches a single entry in memory
         *
         * @param string   $key
         * @param callable $source_func
         * @param mixed    $args
         *
         * @return mixed
         */
        public static function single($key, callable $source_func, $args=null) {
            if (! array_key_exists($key, self::$local)) {
                self::$local[$key] = $source_func($args);
            }
            return self::$local[$key];
        }

        /**
         * The returned rows from $db_func must have their keys be set to the id of the record
         * (the same id as the one passed from the $ids array)
         *
         * @param array      $rows
         * @param callable   $key_func
         * @param callable   $db_func
         * @param array|null $args
         *
         * @return array
         */
        public static function multiple(array $rows, callable $key_func, callable $db_func, $args=null) {

            //this function will preserve the order of the ids
            if (! count($rows)) {
                return [];
            }

            //make a list of keys
            $keys = [];
            foreach ($rows as $k => $row) {
                if (is_array($row)) {
                    $keys[$k] = \call_user_func_array($key_func, $row);
                } else {
                    $keys[$k] = $key_func($row);
                }
            }

            //check local memory
            $valid_rows = [];
            foreach ($rows as $k => $row) {
                $key = $keys[$k];
                if (array_key_exists($key, self::$local)) {
                    $valid_rows[$k] = self::$local[$key];
                    unset($rows[$k]);
                }
            }

            //if not all was pulled from memory
            if (count($rows)) {
                $db_rows = $db_func($rows, $args);
                if (is_array($db_rows) && $db_rows) {
                    $valid_rows += $db_rows;
                    foreach ($db_rows as $k => $row) {
                        self::$local[$keys[$k]] = $row;
                    }
                }

                //if this is not 100% from memory, sort it
                ksort($valid_rows);
            }

            return $valid_rows;
        }

        /**
         * Delete a record from memory
         *
         * @param string $key
         */
        public static function delete($key){
            if (array_key_exists($key, self::$local)) {
                unset(self::$local[$key]);
            }
        }

        /**
         * Delete multiple records from memory
         *
         * @param array $keys
         */
        public static function delete_multi(array $keys){
            if (count($keys)) {
                foreach ($keys as $key) {
                    if (array_key_exists($key, self::$local)) {
                        unset(self::$local[$key]);
                    }
                }
            }
        }

        /**
         * Get record from memory
         *
         * @param string $k key
         *
         * @return mixed
         * @throws exception
         */
        public static function get($k) {
            if (array_key_exists($k, self::$local)) {
                return self::$local[$k];
            } else {
                throw new exception('Variable does not exist in memory');
            }
        }

        /**
         * Set record in memory
         *
         * @param string $k key
         * @param string $v value
         *
         * @return mixed
         */
        public static function set($k, $v) {
            return self::$local[$k] = $v;
        }

        /**
         * Get multiple records from memory
         *
         * @param array $keys
         *
         * @return array
         */
        public static function get_multi(array $keys) {
            $matches = [];
            foreach ($keys as $index => $key) {
                if (array_key_exists($key, self::$local)) {
                    $matches[$index] = self::$local[$key];
                }
            }
            return $matches;
        }

        /**
         * Set multiple records
         *
         * @param array $rows
         */
        public static function set_multi(array $rows) {
            foreach ($rows as $k => $v) {
                self::$local[$k] = $v;
            }
        }

        /**
         * Gets everything in memory
         *
         * @return array
         */
        public static function dump() {
            return self::$local;
        }

        /**
         * Delete everything in memory
         */
        public static function flushall() {
            self::$local = [];
        }

        /**
         * Increment a record's value
         *
         * @param string $key
         * @param int    $offset
         *
         * @return mixed
         */
        public static function increment($key, $offset=1){
            if (array_key_exists($key, self::$local)) {
                self::$local[$key] += $offset;
                return self::$local[$key];
            }
        }

        /**
         * Decrement a record's value
         *
         * @param string $key
         * @param int    $offset
         *
         * @return mixed
         */
        public static function decrement($key, $offset=1){
            if (array_key_exists($key, self::$local)) {
                self::$local[$key] -= $offset;
                return self::$local[$key];
            }
        }
    }