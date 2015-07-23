<?php

    namespace Neoform;

    use Redis as PhpRedis;

    class Redis extends Service\Singleton {

        /**
         * @var Redis\Redis
         */
        protected $redis;

        /**
         * @var string
         */
        protected $connectionPool;

        /**
         * @param string $connectionPool
         */
        public function __construct($connectionPool) {
            $this->connectionPool = $connectionPool;
        }

        /**
         * @return Redis\Redis
         */
        public function get() {
            if (! $this->redis) {
                $this->open();
            }

            return $this->redis;
        }

        /**
         * @return $this
         * @throws Redis\Exception
         */
        public function open() {

            $config = Redis\Config::get();

            if (! $this->connectionPool) {
                if (! $config->getDefaultPoolWrite()) {
                    throw new Redis\Exception("No default write pool set");
                }

                $this->connectionPool = $config->getDefaultPoolWrite();
            }

            if (empty($config->getPools()[$this->connectionPool])) {
                throw new Redis\Exception("Redis server configuration \"{$this->connectionPool}\" does not exist");
            }

            $server = $config->getPools()[$this->connectionPool][array_rand($config->getPools()[$this->connectionPool])];

            try {
                $this->redis = new Redis\Redis;

                // Persistent Connect
                if ($config->isPersistentConnection()) {

                    // IP based connect
                    if ($server['host']) {
                        $this->redis->pconnect(
                            $server['host'],
                            $server['port'],
                            $config->getPersistentConnectionTtl(),
                            $config->getPersistentConnectionId()
                        );

                    // Socket based connect
                    } else if ($server['socket']) {
                        $this->redis->pconnect($server['socket']);
                    } else {
                        throw new Redis\Exception("Redis server configuration \"{$this->connectionPool}\" does not contain a host or a socket.");
                    }
                } else {

                    // IP based connect
                    if (isset($server['host'])) {
                        $this->redis->connect(
                            $server['host'],
                            $server['port'],
                            $config->getPersistentConnectionTtl(),
                            $config->getPersistentConnectionId()
                        );

                    // Socket based connect
                    } else if (isset($server['socket'])) {
                        $this->redis->connect($server['socket']);
                    } else {
                        throw new Redis\Exception("Redis server configuration \"{$this->connectionPool}\" does not contain a host or a socket.");
                    }
                }

                // PHP serializer
                $this->redis->setOption(PhpRedis::OPT_SERIALIZER, PhpRedis::SERIALIZER_PHP);

                // Key prefix
                if ($server['key_prefix']) {
                    $this->redis->setOption(PhpRedis::OPT_PREFIX, "{$server['key_prefix']}:");
                } else if ($config->getKeyPrefix()) {
                    $this->redis->setOption(PhpRedis::OPT_PREFIX, "{$config->getKeyPrefix()}:");
                }

                // Database
                if ($server['database']) {
                    $this->redis->select($server['database']);
                }

            } catch (\RedisException $e) {
                throw new Redis\Exception("Could not create redis instance \"{$this->connectionPool}\" -- {$e->getMessage()}", 0, $e);
            }

            return $this;
        }

        /**
         * @return $this
         */
        public function close() {
            $this->redis->close();
            $this->redis = null;
            return $this;
        }
    }