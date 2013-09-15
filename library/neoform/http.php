<?php

    namespace neoform;

    class http extends core\singleton {

        public static function init($name) {
            throw new \exception('http singleton has not been loaded yet - http::load() must be used first');
        }

        /**
         * @param array $args
         *
         * @return http\model
         */
        public static function load(array $args) {
            $config = config::instance();
            if ($args) {
                return parent::set(new http\model(
                    $args[0],
                    $config['http'],
                    $config['locale'],
                    $args[1],
                    $args[2],
                    $args[3],
                    $args[4],
                    $args[5]
                ));
            } else {
                return parent::set(new http\model(
                    '',
                    $config['http'],
                    $config['locale'],
                    [],
                    [],
                    [],
                    [],
                    []
                ));
            }
        }
    }