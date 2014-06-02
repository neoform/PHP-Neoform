<?php

    namespace neoform\entity\meta\driver;

    use neoform;

    class memory implements \neoform\entity\meta\driver {

        /**
         * Get all unique members of multiple list or get matching members of multiple lists, then delete the lists
         *
         * @param string $pool
         * @param array  $list_keys
         *
         * @return array|null
         */
        public static function pull($pool, array $list_keys) {
            $keys = neoform\cache\driver\memory::list_union($pool, $list_keys);
            neoform\cache\driver\memory::delete_multi($pool, $list_keys);
            return $keys;
        }

        /**
         * Create a list and/or Add a value to a list
         *
         * @param string $pool
         * @param string $cache_key
         * @param array  $list_keys
         */
        public static function push($pool, $cache_key, array $list_keys) {
            neoform\cache\driver\memory::list_append($pool, $cache_key, $list_keys);
        }

        /**
         * Create a list and/or Add a value to a list
         *
         * @param string $pool
         * @param array  $cache_keys
         */
        public static function push_multi($pool, array $cache_keys) {
            foreach ($cache_keys as $cache_key => $list_keys) {
                neoform\cache\driver\memory::list_append($pool, $cache_key, $list_keys);
            }
        }
    }
