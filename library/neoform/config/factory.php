<?php

    namespace neoform;

    /**
     * Creates an instance of a config
     */
    class config_factory implements core_factory {

        public static function init(array $args) {
            return config_dao::get($args ? \current($args) : null);
        }
    }