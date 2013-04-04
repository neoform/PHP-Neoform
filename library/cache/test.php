<?php

    class cache_test extends test_model {

        public function init() {

            $key  = 'cli:cache_unit_test';
            $pool = 'entities';



            // Delete cache
            cache_lib::delete('memcache', $key . ':111', 'entities');
            cache_lib::delete('memcache', $key . ':222', 'entities');




            // Pull data for the first time
            $pulled = false;
            $result = cache_lib::single(
                'memcache',
                $key . ':111',
                $pool,
                function() use (& $pulled) {
                    $pulled = true;
                    return "Hey sexy pants";
                }
            );

            $this->assert_true($pulled, __LINE__);
            $this->assert_true($result === "Hey sexy pants", __LINE__);




            // Pull the same data again, this time it should not get from origin, instead from cache
            $pulled = false;
            $result = cache_lib::single(
                'memcache',
                $key . ':111',
                $pool,
                function() use (& $pulled) {
                    $pulled = true;
                    return "Uh oh...";
                }
            );

            $this->assert_true(! $pulled, __LINE__);
            $this->assert_true($result === "Hey sexy pants", __LINE__);

            $this->assert_true(cache_memory_dao::exists($key . ':111'), __LINE__);
            $this->assert_true(cache_memcache_driver::exists($key . ':111', $pool), __LINE__);




            // Pull multi - only one record should get pulled from origin
            cache_memory_dao::delete($key . ':111'); //don't let it pull from memory

            $pulled = 0;
            $result = cache_lib::multi(
                'memcache',
                [
                    3 => 111,
                    9 => 222,
                ],
                function($id) use ($key) {
                    return $key . ':'. $id;
                },
                $pool,
                function($ids) use (& $pulled) {
                    foreach ($ids as $k => & $v) {
                        $pulled++;
                        $v = 'Looking good, buuuudy';
                    }
                    return $ids;
                }
            );

            $this->assert_true($pulled === 1, __LINE__);
            $this->assert_true(isset($result[3]), __LINE__);
            $this->assert_true(isset($result[9]), __LINE__);
            $this->assert_true($result[3] === "Hey sexy pants", __LINE__);
            $this->assert_true($result[9] === "Looking good, buuuudy", __LINE__);





            // Pull multi - same keys, none should get pulled from origin
            cache_memory_dao::delete($key . ':111'); //don't let it pull from memory
            cache_memory_dao::delete($key . ':222'); //don't let it pull from memory

            $pulled = 0;
            $result = cache_lib::multi(
                'memcache',
                [
                    3 => 111,
                    9 => 222,
                ],
                function($id) use ($key) {
                    return $key . ':'. $id;
                },
                $pool,
                function($ids) use (& $pulled) {
                    foreach ($ids as $k => & $v) {
                        $pulled++;
                        $v = 'Looking good, buuuudy';
                    }
                    return $ids;
                }
            );


            $this->assert_true(cache_memory_dao::exists($key . ':111'), __LINE__);
            $this->assert_true(cache_memory_dao::exists($key . ':222'), __LINE__);
            $this->assert_true(cache_memcache_driver::exists($key . ':111', $pool), __LINE__);
            $this->assert_true(cache_memcache_driver::exists($key . ':222', $pool), __LINE__);

            $this->assert_true($pulled === 0, __LINE__);
            $this->assert_true(isset($result[3]), __LINE__);
            $this->assert_true(isset($result[9]), __LINE__);
            $this->assert_true($result[3] === "Hey sexy pants", __LINE__);
            $this->assert_true($result[9] === "Looking good, buuuudy", __LINE__);

            $this->assert_true(cache_memory_dao::exists($key . ':111'), __LINE__);
            $this->assert_true(cache_memory_dao::exists($key . ':222'), __LINE__);
            $this->assert_true(cache_memcache_driver::exists($key . ':111', $pool), __LINE__);
            $this->assert_true(cache_memcache_driver::exists($key . ':222', $pool), __LINE__);




            // Pull multi - same keys, none should get pulled from origin - same as before, but using memory only
            core::cache_memcache($pool)->delete(cache_memcache_driver::key_prefix() . $key . ':111');
            core::cache_memcache($pool)->delete(cache_memcache_driver::key_prefix() . $key . ':222');

            $this->assert_true(cache_memory_dao::exists($key . ':111'), __LINE__);
            $this->assert_true(cache_memory_dao::exists($key . ':222'), __LINE__);
            $this->assert_true(! cache_memcache_driver::exists($key . ':111', $pool), __LINE__);
            $this->assert_true(! cache_memcache_driver::exists($key . ':222', $pool), __LINE__);

            $pulled = 0;
            $result = cache_lib::multi(
                'memcache',
                [
                    3 => 111,
                    9 => 222,
                ],
                function($id) use ($key) {
                    return $key . ':'. $id;
                },
                $pool,
                function($ids) use (& $pulled) {
                    foreach ($ids as $k => & $v) {
                        $pulled++;
                        $v = 'Sad face';
                    }
                    return $ids;
                }
            );

            $this->assert_true($pulled === 0, __LINE__);
            $this->assert_true(isset($result[3]), __LINE__);
            $this->assert_true(isset($result[9]), __LINE__);
            $this->assert_true($result[3] === "Hey sexy pants", __LINE__);
            $this->assert_true($result[9] === "Looking good, buuuudy", __LINE__);

        }
    }