<?php

    namespace neoform\encrypt;

    use neoform;

    class factory implements neoform\core\factory {

        public static function init(array $args) {
            $config = neoform\core::config()['encrypt'];
            return new model(
                $config['mode'],
                $config['cipher']
            );
        }
    }