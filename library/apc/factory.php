<?php

    class apc_factory implements core_factory {

        public static function init(array $args) {
            return new apc_instance($args ? current($args) : null);
        }
    }