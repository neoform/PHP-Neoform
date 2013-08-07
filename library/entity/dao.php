<?php

    class entity_dao {

        protected static $daos = [];

        public static function __callstatic($name, array $args) {
            if (! isset(self::$daos[$name])) {
                $class_name = "{$name}_dao";
                $config     = core::config()['entity'];

                if (isset($config['overrides'][$name])) {
                    self::$daos[$name] = new $class_name(array_intersect_key($config['overrides'][$name], $config['defaults']));
                } else {
                    self::$daos[$name] = new $class_name($config['defaults']);
                }
            }
            return self::$daos[$name];
        }

        public static function get($name) {
            if (! isset(self::$daos[$name])) {
                $class_name = "{$name}_dao";
                $config     = core::config()['entity'];

                if (isset($config['overrides'][$name])) {
                    self::$daos[$name] = new $class_name(array_intersect_key($config['overrides'][$name], $config['defaults']));
                } else {
                    self::$daos[$name] = new $class_name($config['defaults']);
                }
            }
            return self::$daos[$name];
        }
    }