<?php

    namespace neoform\entity\meta;

    interface driver {

        public static function pull($pool, array $list_keys);

        public static function push($pool, $cache_key, array $list_keys);

        public static function push_multi($pool, array $cache_keys);
    }