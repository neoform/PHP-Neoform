<?php

    namespace neoform;

    class entity_meta_lib {

        /**
         * Delete lists
         *
         * @param string $engine
         * @param string $engine_pool
         * @param array  $list_keys
         *
         * @return array|null
         */
        public static function pull($engine, $engine_pool, $list_keys) {

            if (! $list_keys) {
                return;
            }

            $engine_driver = "neoform\\entity_meta_driver_{$engine}";
            return $engine_driver::pull($engine_pool, $list_keys);
        }

        /**
         * Get multiple joined lists/arrays (via union)
         *
         * @param string $engine
         * @param string $engine_pool
         * @param string $cache_key
         * @param array  $list_keys
         *
         * @return array|null
         */
        public static function push($engine, $engine_pool, $cache_key, array $list_keys) {

            if (! $list_keys) {
                return;
            }

            $engine_driver = "neoform\\entity_meta_driver_{$engine}";
            return $engine_driver::push($engine_pool, $cache_key, $list_keys);
        }

        /**
         * Get multiple joined lists/arrays (via union)
         *
         * @param string $engine
         * @param string $engine_pool
         * @param array  $cache_keys  keys are the cache keys, values are arrays of list keys
         *
         * @return array|null
         */
        public static function push_multi($engine, $engine_pool, array $cache_keys) {

            if (! $cache_keys) {
                return;
            }

            $engine_driver = "neoform\\entity_meta_driver_{$engine}";
            return $engine_driver::push_multi($engine_pool, $cache_keys);
        }
    }