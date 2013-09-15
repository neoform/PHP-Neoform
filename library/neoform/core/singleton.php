<?php

    namespace neoform\core;

    /**
     * Base class for a singleton factory
     */
    abstract class singleton implements factory {

        private static $instances = [];

        // Disable these methods
        final private function __clone() { }
        final private function __construct(array $args=null) {}

        /**
         * @param string $name
         *
         * @return factory
         */
        public static function instance($name=null) {
            if (! isset(self::$instances[static::class][$name])) {
                return self::$instances[static::class][$name] = static::init($name);
            }
            return self::$instances[static::class][$name];
        }

        /**
         * @param string|null $name
         */
        public static function reset($name=null) {
            unset(self::$instances[static::class][$name]);
        }

        /**
         * @param mixed       $instance
         * @param string|null $name
         *
         * @return mixed
         */
        protected static function set($instance, $name=null) {
            return self::$instances[static::class][$name] = $instance;
        }

        /**
         * @param string|null $name
         *
         * @return mixed
         */
        protected static function get($name=null) {
            return self::$instances[static::class][$name];
        }
    }