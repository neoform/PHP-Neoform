<?php

    namespace neoform\entity;

    use neoform;

    class config extends neoform\config\model {

        /**
         * The array key this config file uses in the compiled configs
         *
         * @return string
         */
        public function get_array_key() {
            return 'entity';
        }

        /**
         * Config default values
         *
         * @return array
         */
        protected function defaults() {
            return [

                'defaults' => [
                    // When no entity source engine is defined in definition file, use this engine
                    'source_engine' => null,

                    // Default source engine read connection name
                    'source_engine_pool_read' => null,

                    // Default source engine write connection name
                    'source_engine_pool_write' => null,

                    // If the source engine supports a TTL, the record will expire after this many seconds (0 never expires)
                    'source_engine_ttl' => 0,

                    // When no entity cache engine is defined in definition file, use this engine
                    'cache_engine' => null,

                    // Default cache engine read connection name
                    'cache_engine_pool_read' => null,

                    // Default cache engine write connection name
                    'cache_engine_pool_write' => null,

                    // When no entity cache list engine is defined in definition file, use this engine
                    'cache_meta_engine' => null,

                    // Default cache list engine read connection name
                    'cache_meta_engine_pool_read' => null,

                    // Default cache list engine write connection name
                    'cache_meta_engine_pool_write' => null,

                    // When deleting a cache key, use an expire time in the future instead - this is sometimes necessary
                    // when dealing with master/slave sync lag from the source (eg, SQL) server.
                    // If the slave is unaware of a change to a record that has happened on the master, it's possible
                    // for the source on a slave to be queried, and cached, even if that record has been changed on master
                    // and that change has not yet propagated to the slave, resulting in inaccurate cache.
                    // This value only has effect when the master and slave source is not the same server.
                    'cache_delete_expire_ttl' => 0,

                    // When using a caching engine that supports binary keys, activate this feature.
                    // This will result in smaller cache keys, since the default is to store the hashed values as hex
                    // which is far less efficient
                    'cache_use_binary_keys' => false,
                ],

                'overrides' => [],
            ];
        }

        /**
         * Validate the config values
         *
         * @throws neoform\config\exception
         */
        public function validate() {

        }

        /**
         * Validate the config values after the config has been compiled
         */
        public function validate_post(array $config) {

        }
    }
