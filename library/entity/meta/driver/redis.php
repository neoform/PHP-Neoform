<?php

    class entity_meta_driver_redis implements entity_meta_driver {

        /**
         * Activate a pipelined (batch) query
         *
         * @param string $pool
         */
        public static function pipeline_start($pool) {
            core::redis($pool)->multi();
        }

        /**
         * Execute pipelined (batch) queries and return result
         *
         * @param string $pool
         *
         * @return array result of batch operation
         */
        public static function pipeline_execute($pool) {
            return core::redis($pool)->exec();
        }

        /**
         * Create a list and/or Add a value to a list
         * It is recommended this function be wrapped in a batch operation
         *
         * @param string       $pool
         * @param string|array $key
         * @param mixed        $value
         *
         * @return bool
         */
        public static function list_add($pool, $key, $value) {
            if (is_array($key)) {
                $redis = core::redis($pool);

                // Redis >=2.4 can do multiple adds in one command.
                if (is_array($value)) {
                    foreach ($value as $v) {
                        foreach ($key as $k) {
                            $redis->sAdd($k, $v);
                        }
                    }

                    // Interestingly, PHP doesn't seem to have a max number of function arguments... so this shouldn't cause
                    // any problems...
                    // However PHPRedis seems to have poor support for this... bah.
                    //return call_user_func_array(
                    //    [ core::redis($pool), 'sAdd' ],
                    //    array_merge([ $key ], $value)
                    //);
                } else {
                    foreach ($key as $k) {
                        $redis->sAdd($k, $value);
                    }
                }
            } else {
                // Redis >=2.4 can do multiple adds in one command.
                if (is_array($value)) {
                    $redis = core::redis($pool);
                    foreach ($value as $v) {
                        $redis->sAdd($key, $v);
                    }

                    // Interestingly, PHP doesn't seem to have a max number of function arguments... so this shouldn't cause
                    // any problems...
                    // However PHPRedis seems to have poor support for this... bah.
                    //return call_user_func_array(
                    //    [ core::redis($pool), 'sAdd' ],
                    //    array_merge([ $key ], $value)
                    //);
                } else {
                    return core::redis($pool)->sAdd($key, $value);
                }
            }
        }

        /**
         * Get all members of a list or get matching members of a list
         *
         * @param string       $pool
         * @param string|array $key
         *
         * @return array
         */
        public static function list_get($pool, $key) {
            return core::redis($pool)->sMembers($key);
        }

        /**
         * Get all members of multiple list or get matching members of multiple lists
         *
         * @param string $pool
         * @param array  $keys
         *
         * @return array
         */
        public static function list_get_union($pool, array $keys) {
            return core::redis($pool)->sUnion($keys);
        }

        /**
         * Remove values from a list
         * It is recommended this function be wrapped in a batch operation
         *
         * @param string       $pool
         * @param string|array $key
         * @param mixed        $remove_key
         */
        public static function list_remove($pool, $key, $remove_key) {
            if (is_array($key)) {
                $redis = core::redis($pool);

                // Redis >=2.4 can do multiple removes in one command.
                if (is_array($remove_key)) {
                    foreach ($remove_key as $v) {
                        foreach ($key as $k) {
                            $redis->sRemove($k, $v);
                        }
                    }

                    // PHPRedis has poor support for batch removals.
                    //call_user_func_array(
                    //    [ core::redis($pool), 'sRemove' ],
                    //    array_merge([ $key ], $remove_key)
                    //);
                } else {
                    foreach ($key as $k) {
                        $redis->sRemove($k, $remove_key);
                    }
                }
            } else {
                // Redis >=2.4 can do multiple removes in one command.
                if (is_array($remove_key)) {
                    $redis = core::redis($pool);
                    foreach ($remove_key as $v) {
                        $redis->sRemove($key, $v);
                    }

                    // PHPRedis has poor support for batch removals.
                    //call_user_func_array(
                    //    [ core::redis($pool), 'sRemove' ],
                    //    array_merge([ $key ], $remove_key)
                    //);
                } else {
                    core::redis($pool)->sRemove($key, $remove_key);
                }
            }
        }
    }