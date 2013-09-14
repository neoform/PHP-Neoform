<?php

    namespace neoform\cache;

    use neoform\cache\memory;
    use neoform;

    class test extends neoform\test\model {

        public function init() {

            $key                     = 'cli:cache_unit_test';
            $cache_engine_pool_read  = 'master';
            $cache_engine_pool_write = 'master';
            $cache_engine            = 'redis';
            $key_prefix              = 'cache_test:';
            $cache_engine_class      = "cache_{$cache_engine}_driver";

            // Delete cache
            neoform\cache\lib::delete($cache_engine, $cache_engine_pool_write, "{$key_prefix}{$key}:111");
            neoform\cache\lib::delete($cache_engine, $cache_engine_pool_write, "{$key_prefix}{$key}:222");




            // Pull data for the first time
            $pulled = false;
            $result = neoform\cache\lib::single(
                $cache_engine,
                $cache_engine_pool_read,
                $cache_engine_pool_write,
                "{$key_prefix}{$key}:111",
                function() use (& $pulled) {
                    $pulled = true;
                    return "Hey sexy pants";
                }
            );

            $this->assert_true($pulled, __LINE__);
            $this->assert_true($result === "Hey sexy pants", __LINE__);




            // Pull the same data again, this time it should not get from origin, instead from cache
            $pulled = false;
            $result = neoform\cache\lib::single(
                $cache_engine,
                $cache_engine_pool_read,
                $cache_engine_pool_write,
                "{$key_prefix}{$key}:111",
                function() use (& $pulled) {
                    $pulled = true;
                    return "Uh oh...";
                }
            );

            $this->assert_true(! $pulled, __LINE__);
            $this->assert_true($result === "Hey sexy pants", __LINE__);

            $this->assert_true(memory\dao::exists("{$key_prefix}{$key}:111"), __LINE__);
            $this->assert_true($cache_engine_class::exists($cache_engine_pool_read, "{$key_prefix}{$key}:111"), __LINE__);




            // Pull multi - only one record should get pulled from origin
            memory\dao::delete("{$key_prefix}{$key}:111"); //don't let it pull from memory

            $pulled = 0;
            $result = neoform\cache\lib::multi(
                $cache_engine,
                $cache_engine_pool_read,
                $cache_engine_pool_write,
                [
                    3 => 111,
                    9 => 222,
                ],
                function($id) use ($key, $key_prefix) {
                    return "{$key_prefix}{$key}:{$id}";
                },
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
            memory\dao::delete("{$key_prefix}{$key}:111"); //don't let it pull from memory
            memory\dao::delete("{$key_prefix}{$key}:222"); //don't let it pull from memory

            $pulled = 0;
            $result = neoform\cache\lib::multi(
                $cache_engine,
                $cache_engine_pool_read,
                $cache_engine_pool_write,
                [
                    3 => 111,
                    9 => 222,
                ],
                function($id) use ($key, $key_prefix) {
                    return "{$key_prefix}{$key}:{$id}";
                },
                function($ids) use (& $pulled) {
                    foreach ($ids as $k => & $v) {
                        $pulled++;
                        $v = 'Looking good, buuuudy';
                    }
                    return $ids;
                }
            );


            $this->assert_true(memory\dao::exists("{$key_prefix}{$key}:111"), __LINE__);
            $this->assert_true(memory\dao::exists("{$key_prefix}{$key}:222"), __LINE__);
            $this->assert_true($cache_engine_class::exists($cache_engine_pool_read, "{$key_prefix}{$key}:111"), __LINE__);
            $this->assert_true($cache_engine_class::exists($cache_engine_pool_read, "{$key_prefix}{$key}:222"), __LINE__);

            $this->assert_true($pulled === 0, __LINE__);
            $this->assert_true(isset($result[3]), __LINE__);
            $this->assert_true(isset($result[9]), __LINE__);
            $this->assert_true($result[3] === "Hey sexy pants", __LINE__);
            $this->assert_true($result[9] === "Looking good, buuuudy", __LINE__);

            $this->assert_true(memory\dao::exists("{$key_prefix}{$key}:111"), __LINE__);
            $this->assert_true(memory\dao::exists("{$key_prefix}{$key}:222"), __LINE__);
            $this->assert_true($cache_engine_class::exists($cache_engine_pool_read, "{$key_prefix}{$key}:111"), __LINE__);
            $this->assert_true($cache_engine_class::exists($cache_engine_pool_read, "{$key_prefix}{$key}:222"), __LINE__);




            // Pull multi - same keys, none should get pulled from origin - same as before, but using memory only
            core::$cache_engine($cache_engine_pool_write)->delete("{$key_prefix}{$key}:111");
            core::$cache_engine($cache_engine_pool_write)->delete("{$key_prefix}{$key}:222");

            $this->assert_true(memory\dao::exists("{$key_prefix}{$key}:111"), __LINE__);
            $this->assert_true(memory\dao::exists("{$key_prefix}{$key}:222"), __LINE__);
            $this->assert_true(! $cache_engine_class::exists($cache_engine_pool_read, "{$key_prefix}{$key}:111"), __LINE__);
            $this->assert_true(! $cache_engine_class::exists($cache_engine_pool_read, "{$key_prefix}{$key}:222"), __LINE__);

            $pulled = 0;
            $result = neoform\cache\lib::multi(
                $cache_engine,
                $cache_engine_pool_read,
                $cache_engine_pool_write,
                [
                    3 => 111,
                    9 => 222,
                ],
                function($id) use ($key, $key_prefix) {
                    return "{$key_prefix}{$key}:{$id}";
                },
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