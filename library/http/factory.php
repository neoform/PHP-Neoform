<?php

    class http_factory implements core_factory {

        public static function init(array $args) {
            if ($args) {
                return new http_instance(
                    $args[0],
                    core::config()['http'],
                    core::config()['locale'],
                    $args[1],
                    $args[2],
                    $args[3],
                    $args[4],
                    $args[5]
                );
            } else {
                return new http_instance(
                    '',
                    core::config()['http'],
                    core::config()['locale'],
                    [],
                    [],
                    [],
                    [],
                    []
                );
            }
        }
    }