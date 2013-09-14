<?php

    namespace neoform\output;

    use neoform\core;

    class factory implements core\factory {

        public static function init(array $args) {
            return new instance;
        }
    }