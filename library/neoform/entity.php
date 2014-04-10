<?php

    namespace neoform;

    class entity {

        protected static $daos           = [];
        protected static $daos_cacheless = [];

        protected static $readonly_config = [
            'source_engine_pool_write'     => null, // set to null, since we can't allow any writing to the source

            'cache_engine'                 => null, // no cache engine is used
            'cache_engine_pool_read'       => null,
            'cache_engine_pool_write'      => null,

            'cache_meta_engine'            => null, // no cache engine is used
            'cache_meta_engine_pool_read'  => null,
            'cache_meta_engine_pool_write' => null,

            'cache_delete_expire_ttl'      => null,

        ];

        /**
         * Get a DAO object based on the config files
         *
         * @param string $name name of the dao
         *
         * @return entity\record\dao|entity\link\dao
         */
        public static function dao($name) {
            if (! isset(self::$daos[$name])) {
                $class_name = "\\neoform\\{$name}\\dao";
                $config     = config::instance()['entity'];

                if (isset($config['overrides'][$name])) {
                    self::$daos[$name] = new $class_name($config['overrides'][$name] + $config['defaults']);
                } else {
                    self::$daos[$name] = new $class_name($config['defaults']);
                }
            }
            return self::$daos[$name];
        }

        /**
         * Get a DAO object based on the config files
         *
         * This DAO is a separate instance, and is not a singleton, it will be reinstantiated each time this function
         * is called. Override config data overrides what is found in the config files.
         *
         * @param string $name name of the dao
         * @param array  $override override configurations
         *
         * @return entity\record\dao|entity\link\dao
         */
        public static function dao_override($name, array $override) {
            $class_name = "\\neoform\\{$name}\\dao";
            $config     = config::instance()['entity'];

            if (isset($config['overrides'][$name])) {
                self::$daos[$name] = new $class_name($override + $config['overrides'][$name] + $config['defaults']);
            } else {
                self::$daos[$name] = new $class_name($override + $config['defaults']);
            }
        }

        /**
         * Get a read-only DAO object based on the config files
         *
         * This object cannot be used for writes, only reads. Attempting to write will cause an error.
         *
         * This will directly access the source.
         *
         * @param $name
         *
         * @return entity\record\dao|entity\link\dao
         */
        public static function dao_cacheless($name) {
            if (! isset(self::$daos_cacheless[$name])) {
                self::$daos_cacheless[$name] = self::dao_override($name, self::$readonly_config);
            }
            return self::$daos_cacheless[$name];
        }
    }