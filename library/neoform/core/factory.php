<?php

    namespace neoform\core;

    /**
     * Base interface for a core factory
     */
    interface factory {
        public static function init($name);
    }