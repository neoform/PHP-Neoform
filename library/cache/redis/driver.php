<?php

    class cache_redis_driver implements cache_driver {

        /**
         * Increment the value of a cached entry (only works if the value is an int)
         *
         * @param string  $key
         * @param string  $pool
         * @param integer $offset
         */
        public static function increment($key, $pool, $offset=1) {
            core::cache_redis($pool)->incrBy($key, $offset);
        }

        /**
         * Decrement the value of a cached entry (only works if the value is an int)
         *
         * @param string  $key
         * @param string  $pool
         * @param integer $offset
         */
        public static function decrement($key, $pool, $offset=1) {
            core::cache_redis($pool)->incrBy($key, -$offset);
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
            return (bool) core::cache_redis($pool)->exists($key);
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
            if ($return = core::cache_redis($pool)->sAdd($key, $value)) {
                return $return;
            } else {
                // if for some reason this key is holding a non-list delete it and create a list (this should never happen)
                // this is here just for fault tolerance
                $redis = core::cache_redis($pool);
                $redis->delete($key);
                return $redis->sAdd($key, $value);
            }
        }

        /**
         * Get all members of a list or get matching members of a list
         *
         * @param string $key
         * @param string $pool
         * @param array  $filter list of keys, an intersection is done
         *
         * @return mixed
         */
        public static function list_get($key, $pool, array $filter = null) {
            if ($filter) {
                return array_values(array_intersect(core::cache_redis($pool)->sMembers($key), $filter));
            } else {
                return core::cache_redis($pool)->sMembers($key);
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
            $redis = core::cache_redis($pool);
            foreach ($remove_keys as $remove_key) {
                $redis->sRemove($key, $remove_key);
            }

            // bug in the documentation makes it seem like you can delete multiple keys at the same time. Nope!
            //call_user_func_array([core::cache_redis($pool), 'sRemove'], array_merge([ $key, ], $remove_keys))
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
            $redis = core::cache_redis($pool);
            $data  = $redis->get($key);
            if ($data === false) {
                // since false is potentially a valid result being stored in redis, we must check if the key exists
                return $redis->exists($key) ? [ false, ] : null;
            } else {
                return [
                    $data,
                ];
            }
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
            return core::cache_redis($pool)->set($key, $data, $ttl);
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
            $redis      = core::cache_redis($pool);
            $found_rows = $redis->getMultiple($keys);
            $results    = [];
            $i          = 0;

            // Redis returns the results in order - if the key doesn't exist, false is returned - this problematic
            // since false might be an actual value being stored... therefore we check if the key exists if false is
            // returned
            foreach (array_keys($keys) as $k) {
                if ($found_rows[$i] !== false || $redis->exists($k)) {
                    $results[$k] = $found_rows[$i++];
                }
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

            // Because redis does not have an msetex command at the moment, we need to iterate over the rows and add
            // them one by one..  ugh.
            if ($ttl) {
                $redis = core::cache_redis($pool);
                foreach ($rows as $k => $v) {
                    $redis->set($k, $v, $ttl);
                }
            } else {
                return core::cache_redis($pool)->mset($rows);
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
            return core::cache_redis($pool)->delete($key);
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
                return core::cache_redis($pool)->delete($keys);
            }
        }
    }