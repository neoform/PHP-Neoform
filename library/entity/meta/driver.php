<?php

    interface entity_meta_driver {

        public static function push($pool, $cache_key, array $list_keys);

        public static function push_multi($pool, array $list_keys);

        public static function pull($pool, array $list_keys);
    }