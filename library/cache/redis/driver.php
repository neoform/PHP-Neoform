<?php

    class cache_redis_driver implements cache_driver {

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
         * Increment the value of a cached entry (only works if the value is an int)
         *
         * @param string  $key
         * @param string  $pool
         * @param integer $offset
         */
        public static function increment($key, $pool, $offset=1) {
            core::redis($pool)->incrBy($key, $offset);
        }

        /**
         * Decrement the value of a cached entry (only works if the value is an int)
         *
         * @param string  $key
         * @param string  $pool
         * @param integer $offset
         */
        public static function decrement($key, $pool, $offset=1) {
            core::redis($pool)->incrBy($key, -$offset);
        }

        /**
         * Checks if cached record exists.
         *
         * @param string $key
         * @param string $pool
         *
         * @return boolean
         */
        public static function exists($key, $pool) {
            return (bool) core::redis($pool)->exists($key);
        }

        /**
         * Create a list and/or Add a value to a list
         *
         * @param string $key
         * @param string $pool
         * @param mixed  $value
         *
         * @return bool
         */
        public static function list_add($key, $pool, $value) {
            if ($return = core::redis($pool)->sAdd($key, $value)) {
                return $return;
            } else {
                // if for some reason this key is holding a non-list delete it and create a list (this should never happen)
                // this is here just for fault tolerance
                $redis = core::redis($pool);

                $redis->multi();
                $redis->delete($key);
                $redis->sAdd($key, $value);
                $return = $redis->exec();

                return $return[1];
            }
        }

        /**
         * Get all members of a list or get matching members of a list (via filter array)
         *
         * @param string $key
         * @param string $pool
         * @param array  $filter list of keys, an intersection is done
         *
         * @return array
         */
        public static function list_get($key, $pool, array $filter = null) {
            if ($filter) {
                return array_values(array_intersect(core::redis($pool)->sMembers($key), $filter));
            } else {
                return core::redis($pool)->sMembers($key);
            }
        }

        /**
         * Get all members of multiple list or get matching members of multiple lists (via filter array)
         *
         * @param array  $keys
         * @param string $pool
         * @param array  $filter list of keys, an intersection is done
         *
         * @return array
         */
        public static function list_get_union(array $keys, $pool, array $filter = null) {
            if ($filter) {
                return array_values(array_intersect(core::redis($pool)->sUnion($keys), $filter));
            } else {
                return core::redis($pool)->sUnion($keys);
            }
        }

        /**
         * Remove values from a list
         *
         * @param string $key
         * @param string $pool
         * @param array  $remove_keys
         */
        public static function list_remove($key, $pool, array $remove_keys) {
            $redis = core::redis($pool);
            // Batch execute the deletes
            $redis->multi();
            foreach ($remove_keys as $remove_key) {
                $redis->sRemove($key, $remove_key);
            }
            $redis->exec();

            // bug in the documentation makes it seem like you can delete multiple keys at the same time. Nope!
            //call_user_func_array([core::redis($pool), 'sRemove'], array_merge([ $key, ], $remove_keys))
        }

        /**
         * Gets cached data.
         *  if record does exist, an array with a single element, containing the data.
         *  returns null if record does not exist
         *
         * @param string $key
         * @param string $pool
         *
         * @return array|null returns null if record does not exist.
         */
        public static function get($key, $pool) {
            $redis = core::redis($pool);

            // Batch execute since phpredis returns false if the key doesn't exist on a GET command, which might actually
            // be the stored value... which is not helpful.
            $redis->multi();
            $redis->exists($key);
            $redis->get($key);
            $result = $redis->exec();

            return $result[0] === true ? [ $result[1] ] : null;
        }

        /**
         * @param string       $key
         * @param string       $pool
         * @param mixed        $data
         * @param integer|null $ttl
         *
         * @return mixed
         */
        public static function set($key, $pool, $data, $ttl=null) {
            return core::redis($pool)->set($key, $data, $ttl);
        }

        /**
         * Fetch multiple rows from redis
         *
         * @param array  $keys
         * @param string $pool
         *
         * @return array
         */
        public static function get_multi(array $keys, $pool) {
            $redis = core::redis($pool);

            // Redis returns the results in order - if the key doesn't exist, false is returned - this problematic
            // since false might be an actual value being stored... therefore we check if the key exists if false is
            // returned

            $redis->multi();
            foreach ($keys as $key) {
                $redis->exists($key);
                $redis->get($key);
            }

            $results       = [];
            $redis_results = $redis->exec();
            $i             = 0;
            foreach ($keys as $k => $key) {
                if ($redis_results[$i]) {
                    $results[$k] = $redis_results[$i + 1];
                }

                $i += 2;
            }

            return $results;
        }

        /**
         * Set multiple records at the same time
         *
         * @param array        $rows
         * @param string       $pool
         * @param integer|null $ttl
         *
         * @return mixed
         */
        public static function set_multi(array $rows, $pool, $ttl=null) {
            if ($ttl) {
                $redis = core::redis($pool);
                $redis->multi();
                foreach ($rows as $k => $v) {
                    $redis->set($k, $v, $ttl);
                }
                $redis->exec();
            } else {
                return core::redis($pool)->mset($rows);
            }
        }

        /**
         * Delete a single record
         *
         * @param string $key
         * @param string $pool
         *
         * @return integer the number of keys deleted
         */
        public static function delete($key, $pool) {
            return core::redis($pool)->delete($key);
        }

        /**
         * Delete multiple entries from cache
         *
         * @param array  $keys
         * @param string $pool
         *
         * @return integer the number of keys deleted
         */
        public static function delete_multi(array $keys, $pool) {
            if (count($keys)) {
                reset($keys);
                return core::redis($pool)->delete($keys);
            }
        }
    }