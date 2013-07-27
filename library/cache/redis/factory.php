<?php

    class cache_redis_factory implements core_factory {

        public static function init(array $args) {

            $config = core::config()->redis;
            $name   = $args ? current($args) : null;

            if (empty($config['pools'][$name])) {
                throw new cache_redis_exception("Redis server configuration \"{$name}\" does not exist");
            }

            $server = $config['pools'][$name][array_rand($config['pools'][$name])];

            try {
                $redis = new redis;

                if (isset($server['host'])) {
                    $redis->connect($server['host'], isset($server['port']) ? $server['port'] : 6379);
                } else if (isset($server['socket'])) {
                    $redis->connect($server['socket']);
                } else {
                    throw new cache_redis_exception("Redis server configuration \"{$name}\" does not contain a host or a socket.");
                }

                $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
                $redis->setOption(Redis::OPT_PREFIX, "{$config['key_prefix']}:");

            } catch (RedisException $e) {
                throw new cache_redis_exception("Could not create redis instance \"{$name}\" -- " . $e->getMessage());
            }

            return $redis;
        }
    }