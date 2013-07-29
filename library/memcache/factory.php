<?php

    class memcache_factory implements core_factory {

        public static function init(array $args) {

            $name = count($args) ? current($args) : null;
            $server_pools = core::config()->memcache['pools'];

            if (empty($server_pools[$name])) {
                throw new memcache_exception("Memcached instance configuration \"{$name}\" does not exist");
            }

            try {
                $connection = new memcache_instance($name);

                // big bug in this that causes keys not to match - nate thinks its because there's a null char in the key or something.
                $connection->setOption(memcache_instance::OPT_BINARY_PROTOCOL, false);
                $connection->setOption(memcache_instance::OPT_LIBKETAMA_COMPATIBLE, true);
                //$connection->setOption(memcache_instance::OPT_PREFIX_KEY, core::config()->memcache['key_prefix'] . ':');

            } catch (exception $e) {
                throw new memcache_exception("Could not create memcached instance \"{$name}\" -- " . $e->getMessage());
            }

            $existing_memcache_servers = $connection->getServerList();

            if (count($existing_memcache_servers) !== count($server_pools[$name])) {

                if (is_array($existing_memcache_servers) && count($existing_memcache_servers)) {
                    foreach ($existing_memcache_servers as $node) {
                        $existing_memcache_servers[] = (isset($node['host']) ? (string) $node['host'] : '') . ':' . (isset($node['port']) ? (int) $node['port'] : '');
                    }
                }

                $memcache_servers = [];
                foreach ($server_pools[$name] as $node) {
                    if (isset($node['host']) && isset($node['port']) && ! in_array(((string) $node['host']) . ':' . ((int) $node['port']), $existing_memcache_servers)) {
                        $memcache_servers[] = [
                            (string) $node['host'],
                            (int) $node['port'],
                            (int) $node['weight'],
                        ];
                    }
                }

                if ($memcache_servers) {
                    try {
                        $connection->addServers($memcache_servers);
                    } catch (exception $e) {
                        throw new memcache_exception("Could not create memcached instance \"{$name}\" -- " . $e->getMessage());
                    }
                }

                //if (count($memcache_servers) === 0) {
                //    throw new memcache_exception('Memcached instance configuration "' . $name . '" does not exist');
                //}
            }

            return $connection;
        }
    }