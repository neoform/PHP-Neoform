<?php

    namespace neoform;

    /**
     * Creates an instance of a config
     */
    class config extends core\singleton {
        public static function init($name) {
            return config\dao::get($name);
        }
    }