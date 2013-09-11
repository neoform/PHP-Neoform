<?php

    namespace neoform;

    use redis;
    use redisexception;
    use neoform\redis\exception;

    class redis_factory implements core\factory {

        public static function init(array $args) {

            $config = core::config()['redis'];
            $name   = $args ? current($args) : $config['default_pool_write'];

            if (empty($config['pools'][$name])) {
                throw new exception("Redis server configuration \"{$name}\" does not exist");
            }

            $server = $config['pools'][$name][\array_rand($config['pools'][$name])];

            try {
                $redis = new redis;

                if ($config['persistent_connection']) {
                    if (isset($server['host'])) {
                        $redis->pconnect(
                            $server['host'],
                            isset($server['port']) ? $server['port'] : 6379,
                            isset($config['persistent_connection_timeout']) ? $config['persistent_connection_timeout'] : null,
                            isset($config['persistent_connection_id']) ? $config['persistent_connection_id'] : null
                        );
                    } else if (isset($server['socket'])) {
                        $redis->pconnect($server['socket']);
                    } else {
                        throw new exception("Redis server configuration \"{$name}\" does not contain a host or a socket.");
                    }
                } else {
                    if (isset($server['host'])) {
                        $redis->connect(
                            $server['host'],
                            isset($server['port']) ? $server['port'] : 6379,
                            isset($config['persistent_connection_timeout']) ? $config['persistent_connection_timeout'] : null,
                            isset($config['persistent_connection_id']) ? $config['persistent_connection_id'] : null
                        );
                    } else if (isset($server['socket'])) {
                        $redis->connect($server['socket']);
                    } else {
                        throw new exception("Redis server configuration \"{$name}\" does not contain a host or a socket.");
                    }
                }

                $redis->setOption(redis::OPT_SERIALIZER, \redis::SERIALIZER_PHP);
                $redis->setOption(redis::OPT_PREFIX, isset($server['key_prefix']) ? "{$server['key_prefix']}:" : "{$config['key_prefix']}:");

            } catch (redisexception $e) {
                throw new exception("Could not create redis instance \"{$name}\" -- " . $e->getMessage());
            }

            return $redis;
        }
    }