<?php

    class cache_redis_factory implements core_factory {

        public static function init(array $args) {

            $name   = count($args) ? current($args) : null;
            $config = core::config()->redis;

            if (! isset($config['servers'][$name]) || $config['servers'][$name] === null) {
                throw new cache_redis_exception('Redis server configuration "' . $name . '" does not exist');
            }

            try {
                $redis = new redis();

                $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
                $redis->setOption(Redis::OPT_PREFIX, $config['key_prefix'] . ':');

                if (isset($config['servers'][$name]['host'])) {
                    $redis->connect(
                        $config['servers'][$name]['host'],
                        isset($config['servers'][$name]['port']) ? $config['servers'][$name]['port'] : 6379
                    );
                } else if (isset($config['servers'][$name]['socket'])) {
                    $redis->connect(
                        $config['servers'][$name]['socket']
                    );
                } else {
                    throw new cache_redis_exception('Redis server configuration "' . $name . '" does not contain a host or a socket.');
                }

            } catch (RedisException $e) {
                throw new cache_memcache_exception('Could not create memcached instance "' . $name . ' -- ' . $e->getMessage());
            }

            return $redis;
        }
    }