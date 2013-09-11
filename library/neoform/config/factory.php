<?php

    namespace neoform\config;

    /**
     * Creates an instance of a config
     */
    class factory implements \neoform\core\factory {

        public static function init(array $args) {
            return dao::get($args ? current($args) : null);
        }
    }