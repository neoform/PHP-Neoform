<?php

    namespace neoform;

    /**
     * Class assets
     * @package neoform
     */
    class assets extends core\singleton {
        public static function init($name) {
            return new assets\model(
                config::instance()['assets']
            );
        }
    }
