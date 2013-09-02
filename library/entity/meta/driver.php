<?php

    interface entity_meta_driver {

        public static function list_add($pool, $key, $value);

        public static function list_get($pool, $key);

        public static function list_get_union($pool, array $keys);

        public static function list_remove($pool, $key, $remove_key);
    }