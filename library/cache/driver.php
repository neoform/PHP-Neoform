<?php

    /**
     * Driver interface for cache classes
     */
    interface cache_driver {
        public static function increment($pool, $key, $offset=1);
        public static function decrement($pool, $key, $offset=1);
        public static function exists($pool, $key);
        public static function list_add($pool, $key, $value);
        public static function list_get($pool, $key);
        public static function list_get_union($pool, array $keys);
        public static function list_remove($pool, $key, $remove_key);
        public static function get($pool, $key);
        public static function get_multi($pool, array $keys);
        public static function set($pool, $key, $data, $ttl=null);
        public static function set_multi($pool, array $rows, $ttl=null);
        public static function delete($pool, $key);
        public static function delete_multi($pool, array $keys);
        public static function expire($pool, $key, $ttl=0);
        public static function expire_multi($pool, array $keys, $ttl=0);
    }