<?php

    class entity {

        protected static $daos = [];

        /**
         * Get a DAO object based on the config files
         *
         * @param string $name name of the dao
         *
         * @return record_dao|link_dao
         */
        public static function dao($name) {
            if (! isset(self::$daos[$name])) {
                $class_name = "{$name}_dao";
                $config     = core::config()['entity'];

                if (isset($config['overrides'][$name])) {
                    self::$daos[$name] = new $class_name($config['overrides'][$name] + $config['defaults']);
                } else {
                    self::$daos[$name] = new $class_name($config['defaults']);
                }
            }

            return self::$daos[$name];
        }
    }