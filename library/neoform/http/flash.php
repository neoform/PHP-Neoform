<?php

    namespace neoform\http;

    class flash extends \neoform\core\singleton {

        public static function init($name) {
            return new flash\model(config::instance()['http']['session']);
        }
    }