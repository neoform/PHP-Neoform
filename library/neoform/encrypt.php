<?php

    namespace neoform;

    class encrypt extends core\singleton {

        public static function init($name) {
            $config = config::instance()['encrypt'];
            return new encrypt\model(
                $config['mode'],
                $config['cipher']
            );
        }
    }