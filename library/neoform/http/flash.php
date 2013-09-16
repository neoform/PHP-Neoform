<?php

    namespace neoform\http;

    use neoform\config;

    class flash extends \neoform\core\singleton {

        public static function init($name) {
            return new flash\model(config::instance()['http']['session']);
        }
    }