<?php

    namespace neoform;

    interface entity_meta_driver {

        public static function pull($pool, array $list_keys);

        public static function push($pool, $cache_key, array $list_keys);

        public static function push_multi($pool, array $cache_keys);
    }