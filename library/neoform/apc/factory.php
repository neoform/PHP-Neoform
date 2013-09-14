<?php

    namespace neoform\apc;

    use neoform;

    class factory implements neoform\core\factory {

        public static function init(array $args) {
            return new neoform\apc\instance(neoform\core::config()['apc']);
        }
    }