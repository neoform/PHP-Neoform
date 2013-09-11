<?php

    namespace neoform\http;

    use neoform;

    class factory implements neoform\core\factory {

        public static function init(array $args) {
            if ($args) {
                return new instance(
                    $args[0],
                    neoform\core::config()['http'],
                    neoform\core::config()['locale'],
                    $args[1],
                    $args[2],
                    $args[3],
                    $args[4],
                    $args[5]
                );
            } else {
                return new instance(
                    '',
                    neoform\core::config()['http'],
                    neoform\core::config()['locale'],
                    [],
                    [],
                    [],
                    [],
                    []
                );
            }
        }
    }