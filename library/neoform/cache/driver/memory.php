<?php

    namespace neoform\cache\driver;

    /**
     * Cache variables in memory
     */
    class memory implements \neoform\cache\driver {

        /**
         * @var array holds the cache
         */
        private static $local = [];

        /**
         * Increment a record's value
         *
         * @param string $pool
         * @param string $key
         * @param int    $offset
         *
         * @return mixed
         */
        public static function increment($pool, $key, $offset=1){
            if (array_key_exists($key, self::$local)) {
                self::$local[$key] += $offset;
                return self::$local[$key];
            }
        }

        /**
         * Decrement a record's value
         *
         * @param string $pool
         * @param string $key
         * @param int    $offset
         *
         * @return mixed
         */
        public static function decrement($pool, $key, $offset=1){
            if (array_key_exists($key, self::$local)) {
                self::$local[$key] -= $offset;
                return self::$local[$key];
            }
        }

        /**
         * Checks to see if a key exists in cache
         *
         * @param string $pool
         * @param string $key
         *
         * @return bool
         */
        public static function exists($pool, $key) {
            return array_key_exists($key, self::$local);
        }

        /**
         * Delete a record from memory
         *
         * @param string $pool
         * @param string $key
         */
        public static function delete($pool, $key){
            if (array_key_exists($key, self::$local)) {
                unset(self::$local[$key]);
            }
        }

        /**
         * Delete multiple records from memory
         *
         * @param string $pool
         * @param array  $keys
         */
        public static function delete_multi($pool, array $keys){
            if ($keys) {
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
         * @param string $pool
         * @param string $key
         *
         * @return mixed|null
         */
        public static function get($pool, $key) {
            if (array_key_exists($key, self::$local)) {
                return self::$local[$key];
            }
        }

        /**
         * Set record in memory
         *
         * @param string       $pool
         * @param string       $key
         * @param string       $data
         * @param integer|null $ttl
         *
         * @return mixed
         */
        public static function set($pool, $key, $data, $ttl=null) {
            return self::$local[$key] = $data;
        }

        /**
         * Get multiple records from memory
         *
         * @param string $pool
         * @param array  $keys
         *
         * @return array
         */
        public static function get_multi($pool, array $keys) {
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
         * @param string       $pool
         * @param array        $rows
         * @param integer|null $ttl
         */
        public static function set_multi($pool, array $rows, $ttl=null) {
            foreach ($rows as $k => $v) {
                self::$local[$k] = $v;
            }
        }


        public static function expire($pool, $key, $ttl=0) {
            throw new memory\exception('Expire is not supported by memory');
        }

        public static function expire_multi($pool, array $keys, $ttl=0) {
            throw new memory\exception('Expire is not supported by memory');
        }

        /**
         * Append values to a list
         *
         * @param string $pool
         * @param string $cache_key to be put in the lists
         * @param array  $list_keys
         *
         * @throws memory\exception
         */
        public static function list_append($pool, $cache_key, array $list_keys) {

            foreach ($list_keys as $list_key) {
                if (array_key_exists($list_key, self::$local)) {
                    if (! is_array(self::$local[$list_key])) {
                        throw new memory\exception('Value is not a list');
                    }

                    if (! in_array($cache_key, self::$local[$list_key])) {
                        self::$local[$list_key][] = $cache_key;
                    }
                } else {
                    self::$local[$list_key] = [ $cache_key, ];
                }
            }
        }

        /**
         * Get a union of multiple lists
         *
         * @param string $pool
         * @param array  $list_keys
         *
         * @return array
         */
        public static function list_union($pool, array $list_keys) {

            $list = [];
            $arrs = array_intersect_key(
                self::$local,
                array_flip($list_keys)
            );

            foreach ($arrs as $arr) {
                foreach ($arr as $val) {
                    $list[] = $val;
                }
            }

            return $list;
        }
    }