<?php

    namespace neoform\locale;

    use neoform\core;

    class factory implements core\factory {

        public static function init(array $args) {
            return new instance(core::config()['locale']);
        }
    }