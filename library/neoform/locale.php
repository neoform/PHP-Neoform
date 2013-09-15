<?php

    namespace neoform;

    class locale extends core\singleton {
        public static function init($name) {
            return new locale\instance(config::instance()['locale']);
        }
    }