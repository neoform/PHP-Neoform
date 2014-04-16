<?php

    namespace neoform\entity\meta\driver;

    use neoform;

    class redis implements \neoform\entity\meta\driver {

        /**
         * Get all unique members of multiple list or get matching members of multiple lists, then delete the lists
         *
         * @param string $pool
         * @param array  $list_keys
         *
         * @return array|null
         */
        public static function pull($pool, array $list_keys) {
            return neoform\redis::instance($pool)
                ->multi()            // Batch execute
                ->sUnion($list_keys) // Get all cache keys from the meta lists
                ->delete($list_keys) // Delete those meta lists
                ->exec()[0];         // Return the result of the union
        }

        /**
         * Create a list and/or Add a value to a list
         *
         * @param string $pool
         * @param string $cache_key
         * @param array  $list_keys
         */
        public static function push($pool, $cache_key, array $list_keys) {

            /**
             * Interestingly, PHP doesn't seem to have a max number of function arguments... so this shouldn't cause
             * any problems...
             *
             *  ...5 minutes later....
             *
             * ...However PHPRedis seems to have poor support for this... bah. lame.
             * return \call_user_func_array(
             *     [ neoform\redis::instance($pool), 'sAdd' ],
             *     array_merge([ $key ], $value)
             * );
             */

            $redis = neoform\redis::instance($pool)->multi();
            foreach ($list_keys as $list_key) {
                $redis->sAdd($list_key, $cache_key);
            }
            $redis->exec();
        }

        /**
         * Create a list and/or Add a value to a list
         *
         * @param string $pool
         * @param array  $cache_keys
         */
        public static function push_multi($pool, array $cache_keys) {

            /**
             * Interestingly, PHP doesn't seem to have a max number of function arguments... so this shouldn't cause
             * any problems...
             *
             *  ...5 minutes later....
             *
             * ...However PHPRedis seems to have poor support for this... bah. lame.
             * return \call_user_func_array(
             *     [ neoform\redis::instance($pool), 'sAdd' ],
             *     array_merge([ $key ], $value)
             * );
             */

            $redis = neoform\redis::instance($pool)->multi();
            foreach ($cache_keys as $cache_key => $list_keys) {
                foreach ($list_keys as $list_key) {
                    $redis->sAdd($list_key, $cache_key);
                }
            }
            $redis->exec();
        }
    }
