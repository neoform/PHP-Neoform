<?php

    namespace neoform\http;

    use neoform;

    class flash extends neoform\core\singleton {

        public static function init($name) {
            return new flash\model(neoform\config::instance()['http']['session']);
        }
    }