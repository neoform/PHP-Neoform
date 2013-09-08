<?php

    namespace neoform;

    /**
     * Base interface for a core factory
     */
    interface core_factory {
        public static function init(array $args);
    }