<?php

    namespace Neoform\Service;

    /**
     * Base class for a singleton factory
     */
    abstract class Singleton implements Service {

        /**
         * @var Service[]
         */
        private static $instances = [];

        /**
         * @param string $name
         *
         * @return static
         * @deprecated use self::getService()->get() instead
         */
        public static function instance($name=null) {
            return self::getService($name)->get();
        }

        /**
         * @param string $name
         *
         * @return static
         */
        public static function getService($name) {
            if (! isset(self::$instances[static::class][$name])) {
                return self::$instances[static::class][$name] = new static($name);
            }

            return self::$instances[static::class][$name];
        }
    }