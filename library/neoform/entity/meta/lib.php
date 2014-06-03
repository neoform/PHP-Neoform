<?php

    namespace neoform\entity\meta;

    use neoform;

    class lib {

        /**
         * Get union of multiple lists, then delete the lists
         *
         * @param string $engine
         * @param string $engine_pool
         * @param bool   $cache_engine_memory Use memory cache
         * @param array  $list_keys
         *
         * @return array|null
         */
        public static function pull($engine, $engine_pool, $cache_engine_memory, array $list_keys) {

            if (! $list_keys) {
                return;
            }

            if (! $engine) {
                return $cache_engine_memory ? neoform\entity\meta\driver\memory::pull($engine_pool, $list_keys) : null;
            }

            if ($cache_engine_memory) {
                neoform\entity\meta\driver\memory::pull($engine_pool, $list_keys);
            }

            $engine_driver = "\\neoform\\entity\\meta\\driver\\{$engine}";
            return $engine_driver::pull($engine_pool, $list_keys);
        }

        /**
         * Get multiple joined lists/arrays (via union)
         *
         * @param string $engine
         * @param string $engine_pool
         * @param bool   $cache_engine_memory Use memory cache
         * @param string $cache_key
         * @param array  $list_keys
         *
         * @return array|null
         */
        public static function push($engine, $engine_pool, $cache_engine_memory, $cache_key, array $list_keys) {

            if (! $list_keys) {
                return;
            }

            if (! $engine) {
                return $cache_engine_memory ? neoform\entity\meta\driver\memory::push($engine_pool, $cache_key, $list_keys) : null;
            }

            if ($cache_engine_memory) {
                neoform\entity\meta\driver\memory::push($engine_pool, $cache_key, $list_keys);
            }

            $engine_driver = "\\neoform\\entity\\meta\\driver\\{$engine}";
            return $engine_driver::push($engine_pool, $cache_key, $list_keys);
        }

        /**
         * Get multiple joined lists/arrays (via union)
         *
         * @param string $engine
         * @param string $engine_pool
         * @param bool   $cache_engine_memory Use memory cache
         * @param array  $cache_keys  keys are the cache keys, values are arrays of list keys
         *
         * @return array|null
         */
        public static function push_multi($engine, $engine_pool, $cache_engine_memory, array $cache_keys) {

            if (! $cache_keys) {
                return;
            }

            if (! $engine) {
                return $cache_engine_memory ? neoform\entity\meta\driver\memory::push_multi($engine_pool, $cache_keys) : null;
            }

            if ($cache_engine_memory) {
                neoform\entity\meta\driver\memory::push_multi($engine_pool, $cache_keys);
            }

            $engine_driver = "\\neoform\\entity\\meta\\driver\\{$engine}";
            return $engine_driver::push_multi($engine_pool, $cache_keys);
        }
    }
