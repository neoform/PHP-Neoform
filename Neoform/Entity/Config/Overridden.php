<?php

    namespace Neoform\Entity\Config;

    use Neoform;

    class Overridden extends Neoform\Config\Model {

        /**
         * When no entity source engine is defined in definition file, use this engine
         *
         * @return string
         */
        public function getSourceEngine() {
            return $this->values['source_engine'];
        }

        /**
         * Source engine read connection name
         *
         * @return string
         */
        public function getSourceEnginePoolRead() {
            return $this->values['source_engine_pool_read'];
        }

        /**
         * Source engine write connection name
         *
         * @return string
         */
        public function getSourceEnginePoolWrite() {
            return $this->values['source_engine_pool_write'];
        }

        /**
         * If the source engine supports a TTL, the record will expire after this many seconds (0 never expires)
         *
         * @return integer
         */
        public function getSourceEngineTtl() {
            return $this->values['source_engine_ttl'];
        }

        /**
         * When no entity cache engine is defined in definition file, use this engine
         *
         * @return string
         */
        public function getCacheEngine() {
            return $this->values['cache_engine'];
        }

        /**
         * Cache engine read connection name
         *
         * @return string
         */
        public function getCacheEnginePoolRead() {
            return $this->values['cache_engine_pool_read'];
        }

        /**
         * Cache engine write connection name
         *
         * @return string
         */
        public function getCacheEnginePoolWrite() {
            return $this->values['cache_engine_pool_write'];
        }

        /**
         * When no entity cache list engine is defined in definition file, use this engine
         *
         * @return string
         */
        public function getCacheMetaEngine() {
            return $this->values['cache_meta_engine'];
        }

        /**
         * Cache list engine write connection name
         *
         * @return string
         */
        public function getCacheMetaEnginePool() {
            return $this->values['cache_meta_engine_pool'];
        }

        /**
         * When deleting a cache key, use an expire time in the future instead - this is sometimes necessary
         * when dealing with master/slave sync lag from the source (eg, SQL) server.
         *
         * If the slave is unaware of a change to a record that has happened on the master, it's possible
         * for the source on a slave to be queried, and cached, even if that record has been changed on master
         * and that change has not yet propagated to the slave, resulting in inaccurate cache.
         * This value only has effect when the master and slave source is not the same server.
         *
         * @return integer
         */
        public function getCacheDeleteExpireTtl() {
            return $this->values['cache_delete_expire_ttl'];
        }

        /**
         * When using a caching engine that supports binary keys, activate this feature.
         * This will result in smaller cache keys, since the default is to store the hashed values as hex
         * which is far less efficient
         *
         * @return bool
         */
        public function isCacheUsingBinaryKeys() {
            return (bool) $this->values['cache_use_binary_keys'];
        }
    }