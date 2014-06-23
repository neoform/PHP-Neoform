<?php

    namespace neoform;

    class redis extends core\singleton {

        public static function init($name) {

            $config = config::instance()['redis'];
            $name   = $name ?: $config['default_pool_write'];

            if (empty($config['pools'][$name])) {
                throw new redis\exception("Redis server configuration \"{$name}\" does not exist");
            }

            $server = $config['pools'][$name][array_rand($config['pools'][$name])];

            try {
                $redis = new \redis;

                // Persistent Connect
                if ($config['persistent_connection']) {

                    // IP based connect
                    if ($server['host']) {
                        $redis->pconnect(
                            $server['host'],
                            $server['port'],
                            $config['persistent_connection_timeout'],
                            $config['persistent_connection_id']
                        );

                    // Socket based connect
                    } else if ($server['socket']) {
                        $redis->pconnect($server['socket']);
                    } else {
                        throw new redis\exception("Redis server configuration \"{$name}\" does not contain a host or a socket.");
                    }
                } else {

                    // IP based connect
                    if (isset($server['host'])) {
                        $redis->connect(
                            $server['host'],
                            $server['port'],
                            $config['persistent_connection_timeout'],
                            $config['persistent_connection_id']
                        );

                    // Socket based connect
                    } else if (isset($server['socket'])) {
                        $redis->connect($server['socket']);
                    } else {
                        throw new redis\exception("Redis server configuration \"{$name}\" does not contain a host or a socket.");
                    }
                }

                // PHP serializer
                $redis->setOption(\redis::OPT_SERIALIZER, \redis::SERIALIZER_PHP);

                // Key prefix
                if ($server['key_prefix']) {
                    $redis->setOption(\redis::OPT_PREFIX, "{$server['key_prefix']}:");
                } else if ($config['key_prefix']) {
                    $redis->setOption(\redis::OPT_PREFIX, "{$config['key_prefix']}:");
                }

                // Database
                if ($server['database']) {
                    $redis->select($server['database']);
                }

            } catch (\redisexception $e) {
                throw new redis\exception("Could not create redis instance \"{$name}\" -- " . $e->getMessage());
            }

            return $redis;
        }
    }