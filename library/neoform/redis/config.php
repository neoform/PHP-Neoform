<?php

    namespace neoform\redis;

    use neoform;

    class config extends neoform\config\model {

        /**
         * The array key this config file uses in the compiled configs
         *
         * @return string
         */
        public function get_array_key() {
            return 'redis';
        }

        /**
         * Config default values
         *
         * @return array
         */
        protected function defaults() {
            return [
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
         * @throws neoform\config\exception
         */
        public function validate() {

            if (empty($this->config['default_pool_read'])) {
                throw new neoform\config\exception('"default_pool_read" must be set');
            }

            if (empty($this->config['default_pool_write'])) {
                throw new neoform\config\exception('"default_pool_write" must be set');
            }

            if (empty($this->config['pools']) || ! is_array($this->config['pools']) || ! $this->config['pools']) {
                throw new neoform\config\exception('"pools" must contain at least one server');
            }

            foreach ($this->config['pools'] as $name => &$servers) {
                if (!is_array($servers)) {
                    throw new neoform\config\exception('servers in "pools" must be an array with server info');
                }

                foreach ($servers as &$server) {
                    if (empty($server['host']) && empty($server['socket'])) {
                        throw new neoform\config\exception('server "host" or "socket" must be set');
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
         */
        public function validate_post(array $config) {

        }
    }