<?php

    class redis_factory implements core_factory {

        public static function init(array $args) {

            $config = core::config()->redis;
            $name   = count($args) ? current($args) : null;

            if (! isset($config['servers'][$name]) || $config['servers'][$name] === null) {
                throw new cache_redis_exception('Redis server configuration "' . $name . '" does not exist');
            }

            $server = $config['servers'][$name][mt_rand(0, count($config['servers'][$name]) - 1)];

            try {
                $redis = new redis();

                if (isset($server['host'])) {
                    $redis->connect($server['host'], isset($server['port']) ? $server['port'] : 6379);
                } else if (isset($server['socket'])) {
                    $redis->connect($server['socket']);
                } else {
                    throw new cache_redis_exception('Redis server configuration "' . $name . '" does not contain a host or a socket.');
                }

                $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
                $redis->setOption(Redis::OPT_PREFIX, $config['key_prefix'] . ':');

            } catch (RedisException $e) {
                throw new cache_redis_exception('Could not create redis instance "' . $name . ' -- ' . $e->getMessage());
            }

            return $redis;
        }
    }