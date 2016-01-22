<?php

    namespace Neoform;

    /**
     * Class Entity
     *
     * @package Neoform
     * @deprecated
     */
    class Entity {

        protected static $daos          = [];
        protected static $daosCacheless = [];

        protected static $readonly_config = [
            'source_engine_pool_write'     => null, // set to null, since we can't allow any writing to the source

            'cache_engine'                 => null,
            'cache_engine_pool_read'       => null,
            'cache_engine_pool_write'      => null,

            'cache_meta_engine'            => null,
            'cache_meta_engine_pool'       => null,

            'cache_delete_expire_ttl'      => null,
        ];

        /**
         * Get a DAO object based on the config files
         *
         * This DAO is a separate instance, and is not a singleton, it will be reinstantiated each time this function
         * is called. Override config data overrides what is found in the config files.
         *
         * @param string $name name of the dao
         * @param array  $override override configurations
         *
         * @return Entity\Record\Dao|Entity\Link\Dao
         */
        public static function daoOverride($name, array $override) {
            $className = "\\{$name}\\Dao";
            $config = Entity\Config::get();

            if (isset($config->getOverrides()[$name])) {
                $configValues = $override + $config->getOverrides()[$name] + $config->getDefaults();
            } else {
                $configValues = $override + $config->getDefaults();
            }

            return new $className(new Entity\Config\Overridden($override + $configValues));
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
         * @return Entity\Record\Dao|Entity\Link\Dao
         */
        public static function daoCacheless($name) {
            if (! isset(self::$daosCacheless[$name])) {
                self::$daosCacheless[$name] = self::daoOverride($name, self::$readonly_config);
            }
            return self::$daosCacheless[$name];
        }
    }