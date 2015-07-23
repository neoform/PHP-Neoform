<?php

    namespace Neoform;

    class Memcache extends Service\Singleton {

        /**
         * @var Memcache\Connection
         */
        protected $memcache;

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
         * @return Memcache\Connection
         */
        public function get() {
            if (! $this->memcache) {
                $this->open();
            }

            return $this->memcache;
        }

        /**
         * @return $this
         * @throws Memcache\Exception
         */
        public function open() {

            $config = Memcache\Config::get();

            if (empty($config->getPools()[$this->connectionPool])) {
                throw new Memcache\Exception("Memcached instance configuration \"{$this->connectionPool}\" does not exist");
            }

            try {
                $this->memcache = new Memcache\Connection($config, $this->connectionPool);

                // big bug in this that causes keys not to match - nate thinks its because there's a null char in the key or something.
                $this->memcache->setOption(Memcache\Connection::OPT_BINARY_PROTOCOL, false);
                $this->memcache->setOption(Memcache\Connection::OPT_LIBKETAMA_COMPATIBLE, true);
                $this->memcache->setOption(Memcache\Connection::OPT_PREFIX_KEY, "{$config->getKeyPrefix()}:");

                $existingMemcacheServers = $this->memcache->getServerList();

                if (count($existingMemcacheServers) !== count($config->getPools()[$this->connectionPool])) {

                    if (is_array($existingMemcacheServers) && $existingMemcacheServers) {
                        foreach ($existingMemcacheServers as $node) {
                            $existingMemcacheServers[] = (isset($node['host']) ? (string) $node['host'] : '') . ':' . (isset($node['port']) ? (int) $node['port'] : '');
                        }
                    }

                    $memcacheServers = [];
                    foreach ($config->getPools()[$this->connectionPool] as $node) {
                        if (isset($node['host']) && isset($node['port']) && ! in_array(((string) $node['host']) . ':' . ((int) $node['port']), $existingMemcacheServers)) {
                            $memcacheServers[] = [
                                (string) $node['host'],
                                (int) $node['port'],
                                (int) $node['weight'],
                            ];
                        }
                    }

                    $this->memcache->resetServerList();

                    if ($memcacheServers) {
                        $this->memcache->addServers($memcacheServers);
                    }

                    //if (count($memcacheServers) === 0) {
                    //    throw new memcache_exception('Memcached instance configuration "' . $name . '" does not exist');
                    //}
                }
            } catch (\Exception $e) {
                throw new Memcache\Exception("Could not create memcached instance \"{$this->connectionPool}\" -- {$e->getMessage()}", 0, $e);
            }

            return $this;
        }

        /**
         * @return $this
         */
        public function close() {
            $this->memcache = null;
            return $this;
        }
    }