<?php

    namespace Neoform\Redis\Config;

    use Neoform;

    class Builder extends Neoform\Config\Builder {

        /**
         * @return Neoform\Redis\Config
         */
        public function build() {
            return new Neoform\Redis\Config($this->configValues);
        }

        /**
         * Config default values
         *
         * @return array
         */
        protected function defaults() {
            return [
                'key_prefix' => null,

                'default_pool_read'  => null,
                'default_pool_write' => null,

                'persistent_connection'         => false,
                'persistent_connection_timeout' => 0,
                'persistent_connection_id'      => null,

                'pools' => [],
            ];
        }

        /**
         * Validate the config values
         *
         * @throws Neoform\Config\Exception
         */
        public function validate() {

            if (empty($this->configValues['default_pool_read'])) {
                throw new Neoform\Config\Exception('"default_pool_read" must be set');
            }

            if (empty($this->configValues['default_pool_write'])) {
                throw new Neoform\Config\Exception('"default_pool_write" must be set');
            }

            if (empty($this->configValues['pools']) || ! is_array($this->configValues['pools']) || ! $this->configValues['pools']) {
                throw new Neoform\Config\Exception('"pools" must contain at least one server');
            }

            foreach ($this->configValues['pools'] as $name => &$servers) {
                if (!is_array($servers)) {
                    throw new Neoform\Config\Exception('servers in "pools" must be an array with server info');
                }

                foreach ($servers as &$server) {
                    if (empty($server['host']) && empty($server['socket'])) {
                        throw new Neoform\Config\Exception('server "host" or "socket" must be set');
                    }

                    if (empty($server['host'])) {
                        $server['host'] = null;
                    }

                    if (empty($server['socket'])) {
                        $server['socket'] = null;
                    }

                    if (empty($server['port'])) {
                        $server['port'] = 6379;
                    }

                    if (empty($server['database'])) {
                        $server['database'] = null;
                    }

                    if (empty($server['key_prefix'])) {
                        $server['key_prefix'] = null;
                    }
                }
            }
        }

        /**
         * Validate the config values after the config has been compiled
         *
         * @param Neoform\Config\Collection $configs
         */
        public function validatePost(Neoform\Config\Collection $configs) {

        }
    }