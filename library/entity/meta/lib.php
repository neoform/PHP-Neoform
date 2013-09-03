<?php

    class entity_meta_lib {

        /**
         * Activate a pipelined (batch) query
         *
         * @param string $engine
         * @param string $engine_pool
         */
        public static function pipeline_start($engine, $engine_pool) {
            $engine_driver = "entity_meta_driver_{$engine}";
            $engine_driver::pipeline_start($engine_pool);
        }

        /**
         * Execute pipelined (batch) queries and return result
         *
         * @param string $engine
         * @param string $engine_pool
         *
         * @return mixed result of batch operation
         */
        public static function pipeline_execute($engine, $engine_pool) {
            $engine_driver = "entity_meta_driver_{$engine}";
            return $engine_driver::pipeline_execute($engine_pool);
        }

        /**
         * Get a segment of a list/array
         *
         * @param string $engine
         * @param string $engine_pool
         * @param string $list_key
         *
         * @return array|null
         */
        public static function list_get($engine, $engine_pool, $list_key) {

            if (! $list_key) {
                return;
            }

            $engine_driver = "entity_meta_driver_{$engine}";
            return $engine_driver::list_get($engine_pool, $list_key);
        }

        /**
         * Get a segment of multiple joined lists/arrays (via union)
         *
         * @param string $engine
         * @param string $engine_pool
         * @param array  $list_keys
         *
         * @return array|null
         */
        public static function list_get_union($engine, $engine_pool, array $list_keys) {

            if (! $list_keys) {
                return;
            }

            $engine_driver = "entity_meta_driver_{$engine}";
            return $engine_driver::list_get_union($engine_pool, $list_keys);
        }

        /**
         * Create a list and/or Add a value to a list
         *
         * @param string       $engine
         * @param string       $engine_pool
         * @param string|array $list_key
         * @param mixed|array  $value
         */
        public static function list_add($engine, $engine_pool, $list_key, $value) {
            $engine = "entity_meta_driver_{$engine}";
            $engine::list_add($engine_pool, $list_key, $value);
        }

        /**
         * Remove values from a list
         *
         * @param string       $engine
         * @param string       $engine_pool
         * @param string|array $list_key
         * @param array        $remove_keys
         */
        public static function list_remove($engine, $engine_pool, $list_key, $remove_keys) {

            if (! $remove_keys) {
                return;
            }

            $engine_driver = "entity_meta_driver_{$engine}";
            $engine_driver::list_remove($engine_pool, $list_key, $remove_keys);
        }
    }