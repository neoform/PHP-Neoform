<?php

    /**
     * Driver interface for cache classes
     */
    interface cache_driver {
        public static function increment($key, $pool, $offset=1);
        public static function decrement($key, $pool, $offset=1);
        public static function exists($key, $pool);
        public static function list_add($key, $pool, $value);
        public static function list_get($key, $pool, array $filter=null);
        public static function list_get_union(array $keys, $pool, array $filter=null);
        public static function list_remove($key, $pool, array $remove_keys);
        public static function get($key, $pool);
        public static function get_multi(array $keys, $pool);
        public static function set($key, $pool, $data, $ttl=null);
        public static function set_multi(array $rows, $pool, $ttl=null);
        public static function delete($key, $pool);
        public static function delete_multi(array $keys, $pool);
    }